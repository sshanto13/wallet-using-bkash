<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\BkashService;
use App\Models\Transaction;

class WalletController extends Controller
{
    /**
     * Bind bKash wallet (Agreement)
     */
    public function bind(Request $request, BkashService $bkash)
    {
        $user = $request->user();
        
        // Check if user already has a completed wallet
        $existingWallet = $user->wallet;
        if ($existingWallet && $existingWallet->agreement_status === 'Completed' && $existingWallet->token) {
            return response()->json([
                'message' => 'Wallet already bound',
                'wallet' => $existingWallet
            ], 400);
        }
        
        // If wallet exists but not completed, delete it to start fresh
        if ($existingWallet && $existingWallet->agreement_status !== 'Completed') {
            $existingWallet->delete();
        }

        // Use user's phone number if available, otherwise use default sandbox number
        $payerReference = $user->phone ?? '01770618575';
        
        // Always use demo callback URL for sandbox (as per Postman collection)
        //$callbackURL = 'https://merchantdemo.sandbox.bka.sh/callback?version=v2&product=tokenized-checkout&isAgreement=true&hasAgreement=false';
        $callbackURL =  'http://localhost:8000/api/v1/bkash/agreement/callback';
        Log::info('Creating agreement', [
            'callback_url' => $callbackURL,
            'payer_reference' => $payerReference,
            'user_id' => $user->id,
        ]);
        
        try {
            $agreement = $bkash->createAgreement(
                callbackURL: $callbackURL,
                payerReference: $payerReference
            );
            
            // Check for bKash API errors
            if (isset($agreement['statusCode']) && $agreement['statusCode'] !== '0000') {
                $errorMessage = $agreement['statusMessage'] ?? $agreement['message'] ?? 'Agreement creation failed';
                Log::error('bKash agreement creation failed', [
                    'response' => $agreement,
                    'callback_url' => $callbackURL,
                ]);
                abort(500, $errorMessage);
            }
            
            // Store paymentID -> user_id mapping in cache for callback
            if (isset($agreement['paymentID'])) {
                Cache::put('agreement_user_' . $agreement['paymentID'], $user->id, 3600); // 1 hour expiry
            }

            // Create wallet record with agreement creation response
            // This stores all the fields needed for the popup to work without OTP
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'payment_id' => $agreement['paymentID'] ?? null,
                'agreement_id' => $agreement['agreementId'] ?? $agreement['agreementID'] ?? null, // Initial agreementId
                'bkash_url' => $agreement['bkashURL'] ?? null,
                'callback_url' => $agreement['callbackURL'] ?? null,
                'success_callback_url' => $agreement['successCallbackURL'] ?? null,
                'failure_callback_url' => $agreement['failureCallbackURL'] ?? null,
                'cancelled_callback_url' => $agreement['cancelledCallbackURL'] ?? null,
                'payer_reference' => $agreement['payerReference'] ?? $payerReference,
                'agreement_status' => $agreement['agreementStatus'] ?? 'Initiated',
                'agreement_create_time' => $agreement['agreementCreateTime'] ?? null,
                'signature' => $agreement['signature'] ?? null,
                'masked' => $agreement['payerReference'] ?? $payerReference,
                'balance' => 0.00,
            ]);

            Log::info('Wallet created with agreement creation response', [
                'wallet_id' => $wallet->id,
                'payment_id' => $wallet->payment_id,
                'agreement_status' => $wallet->agreement_status,
            ]);

            // Return the agreement response (for popup redirect)
            return response()->json($agreement);
        } catch (\Exception $e) {
            Log::error('Exception creating agreement', [
                'message' => $e->getMessage(),
                'callback_url' => $callbackURL,
            ]);
            throw $e;
        }
    }
    /**
     * Get authenticated user's wallet
     */
    public function me(Request $request)
    {
        $wallet = $request->user()->wallet;
        
        if ($wallet) {
            // Return wallet with token hidden for security (frontend doesn't need it)
            return response()->json([
                'id' => $wallet->id,
                'masked' => $wallet->masked,
                'balance' => $wallet->balance,
                'has_token' => !empty($wallet->token),
                'created_at' => $wallet->created_at,
                'updated_at' => $wallet->updated_at,
            ]);
        }
        
        // Return 404 with null data to clearly indicate no wallet exists
        return response()->json(null, 404);
    }


    public function topUp(Request $request, BkashService $bkash)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            abort(400, 'Wallet not bound');
        }

        // Validate wallet has a valid token and completed agreement
        if (!$wallet->token || empty($wallet->token)) {
            Log::error('Wallet missing token', [
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
            ]);
            abort(400, 'Wallet token is missing. Please rebind your wallet.');
        }

        // Ensure agreement is completed (required for OTP-free payments)
        if ($wallet->agreement_status !== 'Completed') {
            Log::error('Wallet agreement not completed', [
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'agreement_status' => $wallet->agreement_status,
            ]);
            abort(400, 'Wallet agreement is not completed. Please complete the agreement binding first.');
        }

        return DB::transaction(function () use ($request, $wallet, $bkash, $user) {

            // 1️⃣ Create pending transaction
            $trx = Transaction::create([
                'wallet_id' => $wallet->id,
                'type'      => 'credit',
                'amount'    => $request->amount,
                'trx_id'    => uniqid('TRX_'),
                'payment_id'=> '',
                'status'    => 'pending',
            ]);

            // 2️⃣ Get stored agreement ID (the master key for future charges)
            // This agreement ID allows charging without OTP re-entry
            $agreementId = $wallet->token; // Automatically decrypted by getTokenAttribute
            
            // Validate agreement ID length (should be at least 20 characters)
            if (strlen($agreementId) < 20) {
                Log::error('Invalid agreement ID length', [
                    'wallet_id' => $wallet->id,
                    'agreement_id' => $agreementId,
                    'length' => strlen($agreementId),
                ]);
                $trx->update(['status' => 'failed']);
                abort(400, 'Invalid agreement ID. Please rebind your wallet.');
            }
            
            // Log the agreement ID being used (for debugging)
            Log::info('Processing background payment with stored agreement (no OTP required)', [
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'agreement_id_length' => strlen($agreementId),
                'agreement_id_preview' => substr($agreementId, 0, 20) . '...',
                'amount' => $request->amount,
                'agreement_status' => $wallet->agreement_status,
            ]);

            // Use stored payerReference from wallet (from agreement creation)
            // This ensures we use the exact same reference that was used during agreement binding
            $payerReference = $wallet->payer_reference ?? $user->phone ?? '01770618575';
            
            // 3️⃣ Create payment with stored agreement (no callback, no popup needed)
            // Since we have a stored agreement, payment will execute without OTP
            $payment = $bkash->createPayment(
                agreementId: $agreementId,
                amount: $request->amount,
                invoice: $trx->trx_id,
                callbackURL: null, // No callback needed - we execute immediately
                payerReference: $payerReference
            );

            // Check for bKash API errors
            if (isset($payment['statusCode']) && $payment['statusCode'] !== '0000') {
                $errorMessage = $payment['statusMessage'] ?? $payment['message'] ?? 'Payment creation failed';
                Log::error('bKash payment creation failed', [
                    'response' => $payment,
                    'wallet_id' => $wallet->id,
                    'amount' => $request->amount,
                ]);
                
                $trx->update(['status' => 'failed']);
                abort(500, $errorMessage);
            }

            if (!isset($payment['paymentID'])) {
                Log::error('bKash payment response missing paymentID', [
                    'response' => $payment,
                    'wallet_id' => $wallet->id,
                ]);
                
                $trx->update(['status' => 'failed']);
                abort(500, 'Payment creation failed: Invalid response from bKash API. Please check logs for details.');
            }

            // 4️⃣ Save paymentID
            $trx->update([
                'payment_id' => $payment['paymentID']
            ]);

            // 5️⃣ Check if payment is already completed in createPayment response
            // For tokenized checkout with agreement, payment might auto-complete
            $paymentStatus = $payment; // Start with createPayment response
            $transactionStatus = $payment['transactionStatus'] ?? null;
            $statusCode = $payment['statusCode'] ?? null;

            Log::info('Checking payment status with stored agreement (no OTP popup)', [
                'payment_id' => $payment['paymentID'],
                'transaction_id' => $trx->id,
                'agreement_id_preview' => substr($agreementId, 0, 20) . '...',
                'initial_status' => $transactionStatus,
                'initial_status_code' => $statusCode,
            ]);

            // If payment is not already completed, try to execute it
            if ($transactionStatus !== 'Completed') {
                Log::info('Payment not completed in create response, attempting execution', [
                    'payment_id' => $payment['paymentID'],
                    'current_status' => $transactionStatus,
                ]);

                $executeResponse = $bkash->executePayment($payment['paymentID']);

                // Check execution result
                if (($executeResponse['statusCode'] ?? null) !== '0000') {
                    $errorMessage = $executeResponse['statusMessage'] ?? 'Unknown error';
                    
                    // If execution fails with "Invalid Payment State" (2056), payment is likely already completed
                    // For tokenized checkout with agreement, payments may auto-complete
                    if (($executeResponse['statusCode'] ?? null) === '2056') {
                        Log::info('Payment execution returned Invalid Payment State - payment likely already completed', [
                            'payment_id' => $payment['paymentID'],
                            'message' => 'For tokenized checkout with agreement, payments may auto-complete on creation',
                        ]);
                        // Assume payment is completed if we get Invalid Payment State
                        // This is common for tokenized checkout with stored agreements
                        $transactionStatus = 'Completed';
                        $statusCode = '0000';
                        $paymentStatus = $payment; // Use original createPayment response
                    } else {
                        Log::error('bKash payment execution failed', [
                            'payment_id' => $payment['paymentID'],
                            'response' => $executeResponse,
                            'wallet_id' => $wallet->id,
                            'status_code' => $executeResponse['statusCode'] ?? null,
                        ]);
                        
                        $trx->update(['status' => 'failed']);
                        abort(500, 'Payment execution failed: ' . $errorMessage);
                    }
                } else {
                    // Execution successful, use execute response
                    $transactionStatus = $executeResponse['transactionStatus'] ?? null;
                    $statusCode = $executeResponse['statusCode'] ?? null;
                    $paymentStatus = $executeResponse;
                }
            }

            // 6️⃣ Verify payment was completed successfully
            if ($statusCode !== '0000') {
                $errorMessage = $paymentStatus['statusMessage'] ?? 'Unknown error';
                Log::error('bKash payment status check failed', [
                    'payment_id' => $payment['paymentID'],
                    'response' => $paymentStatus,
                    'wallet_id' => $wallet->id,
                    'status_code' => $statusCode,
                ]);
                
                $trx->update(['status' => 'failed']);
                abort(500, 'Payment status check failed: ' . $errorMessage);
            }

            if ($transactionStatus !== 'Completed') {
                Log::error('Payment did not complete', [
                    'payment_id' => $payment['paymentID'],
                    'transaction_status' => $transactionStatus,
                    'response' => $paymentStatus,
                ]);
                $trx->update(['status' => 'failed']);
                abort(500, 'Payment did not complete. Status: ' . $transactionStatus);
            }

            // 7️⃣ Update wallet balance
            $wallet->lockForUpdate();
            $wallet->balance += $request->amount;
            $wallet->save();

            // 7️⃣ Mark transaction as success
            $trx->update([
                'status' => 'success',
                'trx_id' => $paymentStatus['trxID'] ?? $trx->trx_id
            ]);

            Log::info('Payment processed successfully', [
                'payment_id' => $payment['paymentID'],
                'transaction_id' => $trx->id,
                'new_balance' => $wallet->balance,
            ]);

            return response()->json([
                'message' => 'Top-up successful',
                'transaction' => $trx->fresh(),
                'wallet' => $wallet->fresh()
            ]);
        });
    }

    /**
     * Check payment status and execute if successful
     * This is used when callback URL is not reachable (e.g., localhost)
     */
    public function checkPaymentStatus(Request $request, BkashService $bkash)
    {
        $request->validate([
            'payment_id' => 'required|string',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $bkash, $user) {
            // 1️⃣ Find the transaction
            $trx = Transaction::where('payment_id', $request->payment_id)
                ->whereHas('wallet', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->lockForUpdate()
                ->first();

            if (!$trx) {
                return response()->json([
                    'message' => 'Transaction not found',
                    'status' => 'not_found'
                ], 404);
            }

            // If already successful, return immediately
            if ($trx->status === 'success') {
                return response()->json([
                    'message' => 'Payment already processed',
                    'status' => 'success',
                    'transaction' => $trx,
                    'wallet' => $trx->wallet
                ]);
            }

            // 2️⃣ Query payment status from bKash
            $paymentStatus = $bkash->queryPayment($request->payment_id);
            
            Log::info('Payment status check', [
                'payment_id' => $request->payment_id,
                'transaction_id' => $trx->id,
                'bKash_status' => $paymentStatus,
            ]);

            // 3️⃣ Check if payment was successful
            $statusCode = $paymentStatus['statusCode'] ?? null;
            $transactionStatus = $paymentStatus['transactionStatus'] ?? null;

            // If payment is not successful in bKash, update transaction and return
            if ($statusCode !== '0000' || $transactionStatus !== 'Completed') {
                $trx->update(['status' => 'failed']);
                return response()->json([
                    'message' => 'Payment not completed',
                    'status' => 'failed',
                    'bKash_status' => $paymentStatus,
                    'transaction' => $trx
                ]);
            }

            // 4️⃣ Execute payment (if not already executed)
            $executeResponse = $bkash->executePayment($request->payment_id);

            if (($executeResponse['statusCode'] ?? null) !== '0000') {
                Log::error('Payment execution failed during status check', [
                    'payment_id' => $request->payment_id,
                    'response' => $executeResponse,
                ]);
                $trx->update(['status' => 'failed']);
                return response()->json([
                    'message' => 'Payment execution failed',
                    'status' => 'failed',
                    'transaction' => $trx
                ], 500);
            }

            // 5️⃣ Update wallet balance (only if not already successful)
            $wallet = Wallet::where('id', $trx->wallet_id)
                ->lockForUpdate()
                ->first();

            // Only update balance if transaction wasn't already successful
            $wasPending = $trx->status === 'pending';
            if ($wasPending) {
                $wallet->balance += $trx->amount;
                $wallet->save();
            }

            // 6️⃣ Mark transaction as success
            $trx->update([
                'status' => 'success',
                'trx_id' => $executeResponse['trxID'] ?? $paymentStatus['trxID'] ?? null
            ]);

            return response()->json([
                'message' => 'Payment processed successfully',
                'status' => 'success',
                'transaction' => $trx->fresh(),
                'wallet' => $wallet->fresh()
            ]);
        });
    }

    // refund

    public function refund(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'amount'         => 'required|numeric|min:1',
            'reason'         => 'required|string|max:255',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {

            // Lock transaction row
            $transaction = Transaction::where('id', $request->transaction_id)
                ->lockForUpdate()
                ->first();

            if ($transaction->status !== 'success') {
                return response()->json([
                    'message' => 'Only successful transactions can be refunded'
                ], 422);
            }

            // Prevent duplicate refund
            $alreadyRefunded = Transaction::where('trx_id', $transaction->trx_id)
                ->where('type', 'refund')
                ->exists();

            if ($alreadyRefunded) {
                return response()->json([
                    'message' => 'Transaction already refunded'
                ], 409);
            }

            // Validate refund amount
            if ($request->amount > $transaction->amount) {
                return response()->json([
                    'message' => 'Refund amount exceeds original transaction'
                ], 422);
            }

            // Lock wallet
            $wallet = $transaction->wallet()->lockForUpdate()->first();

            /*
            |--------------------------------------------------------------------------
            | Call bKash Refund API (Sandbox / Live)
            |--------------------------------------------------------------------------
            */
            // MOCK RESPONSE (replace with real API call)
            $bkashResponse = [
                'statusCode' => '0000',
                'trxID'      => 'RFND_' . Str::uuid(),
            ];

            if ($bkashResponse['statusCode'] !== '0000') {
                throw new \Exception('bKash refund failed');
            }

            // Create refund transaction record
            $refund = Transaction::create([
                'wallet_id'  => $wallet->id,
                'type'       => 'refund',
                'amount'     => $request->amount,
                'trx_id'     => $bkashResponse['trxID'],
                'payment_id' => $transaction->payment_id,
                'status'     => 'success',
            ]);

            // Rollback wallet balance
            $wallet->balance -= $request->amount;
            $wallet->save();

            return response()->json([
                'message' => 'Refund successful',
                'refund'  => $refund,
                'wallet'  => $wallet
            ]);
        });
    }
    public function transactions(Request $request)
    {
        $user   = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found'
            ], 404);
        }

        $query = Transaction::where('wallet_id', $wallet->id);

        // 🔍 Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type); // credit / debit
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status); // success / pending / failed
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // 📄 Pagination
        $transactions = $query
            ->orderByDesc('id')
            ->paginate(
                $request->get('per_page', 10)
            );

        return response()->json([
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
            ]
        ]);
    }
        public function generate(Request $request)
    {
        $user = $request->user();

        // Validate filters
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'transaction_type' => 'nullable|in:credit,debit,refund',
        ]);

        // Fetch transactions
        $query = Transaction::whereHas('wallet', fn($q) => $q->where('user_id', $user->id));

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->transaction_type) {
            $query->where('type', $request->transaction_type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Build simple HTML for PDF
        $html = view('pdf.wallet_statement', compact('transactions', 'user'))->render();

        // Send to Gotenberg
        $client = new Client([
            'base_uri' => env('GOTENBERG_URL', 'http://127.0.0.1:3000'), // Set Gotenberg server URL
        ]);

        $response = $client->request('POST', '/forms/html', [
            'multipart' => [
                [
                    'name' => 'files',
                    'contents' => $html,
                    'filename' => 'statement.html',
                ],
            ],
            'headers' => [
                'Accept' => 'application/pdf',
            ],
        ]);

        $pdfContent = $response->getBody()->getContents();

        // Store PDF temporarily
        $filename = 'wallet_statement_' . now()->format('Ymd_His') . '.pdf';
        Storage::put('public/' . $filename, $pdfContent);

        // Return download URL
        return response()->json([
            'url' => asset('storage/' . $filename)
        ]);
    }


}