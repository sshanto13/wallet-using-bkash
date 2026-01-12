<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BkashService;
use App\Models\Transaction;

class WalletController extends Controller
{
    /**
     * Bind bKash wallet (Agreement)
     */
    public function bind(BkashService $bkash)
    {
        $agreement = $bkash->createAgreement(
            callbackURL: route('bkash.agreement.callback')
        );

        return response()->json($agreement);
    }
    /**
     * Get authenticated user's wallet
     */
    public function me(Request $request)
    {
        return response()->json(
            $request->user()->wallet
        );
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

        return DB::transaction(function () use ($request, $wallet, $bkash) {

            // 1️⃣ Create pending transaction
            $trx = Transaction::create([
                'wallet_id' => $wallet->id,
                'type'      => 'credit',
                'amount'    => $request->amount,
                'trx_id'    => uniqid('TRX_'),
                'payment_id'=> '',
                'status'    => 'pending',
            ]);

            // 2️⃣ Create bKash payment
            $payment = $bkash->createPayment(
                agreementId: $wallet->token,
                amount: $request->amount,
                invoice: $trx->trx_id
            );

            if (!isset($payment['paymentID'])) {
                abort(500, 'Payment creation failed');
            }

            // 3️⃣ Save paymentID
            $trx->update([
                'payment_id' => $payment['paymentID']
            ]);

            return response()->json([
                'redirect_url' => $payment['bkashURL'],
                'payment_id'   => $payment['paymentID']
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
        $user = Auth::user();

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