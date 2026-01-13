<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\BkashService;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class BkashCallbackController extends Controller
{
    /**
     * Handle bKash agreement callback (for wallet binding)
     */
    public function agreementCallback(Request $request, BkashService $bkash)
    {
        if ($request->status !== 'success') {
            // Return HTML page for cancelled/failed agreement
            return response()->view('bkash.callback-error', [
                'message' => 'Agreement cancelled or failed',
                'redirectUrl' => url('/wallet')
            ], 400);
        }

        return DB::transaction(function () use ($request, $bkash) {
            // Get user_id from cache (stored during bind)
            $userId = Cache::get('agreement_user_' . $request->paymentID);
            
            if (!$userId) {
                abort(400, 'Invalid or expired agreement request');
            }

            // Execute agreement
            $response = $bkash->executeAgreement($request->paymentID);

            if (($response['statusCode'] ?? null) !== '0000') {
                \Log::error('Agreement execution failed', [
                    'paymentID' => $request->paymentID,
                    'response' => $response,
                ]);
                abort(500, 'Agreement execution failed: ' . ($response['statusMessage'] ?? 'Unknown error'));
            }

            // Validate agreementID exists in response (check both camelCase and uppercase)
            $agreementID = $response['agreementID'] ?? $response['agreementId'] ?? null;
            if (!$agreementID || empty($agreementID)) {
                \Log::error('Agreement ID missing in response', [
                    'paymentID' => $request->paymentID,
                    'response' => $response,
                ]);
                abort(500, 'Agreement ID not found in response');
            }

            // Find existing wallet by payment_id (created during bind)
            $wallet = Wallet::where('payment_id', $request->paymentID)
                ->where('user_id', $userId)
                ->first();

            if (!$wallet) {
                \Log::error('Wallet not found for payment ID', [
                    'payment_id' => $request->paymentID,
                    'user_id' => $userId,
                ]);
                Cache::forget('agreement_user_' . $request->paymentID);
                abort(404, 'Wallet not found for this agreement');
            }

            //  Validate agreementID before storing
            // Check both camelCase and uppercase variants
            $agreementID = $response['agreementID'] ?? $response['agreementId'] ?? null;
            
            \Log::info('bKash executeAgreement response', [
                'full_response' => $response,
                'agreementID' => $agreementID,
                'agreementID_length' => $agreementID ? strlen($agreementID) : 0,
                'wallet_id' => $wallet->id,
            ]);
            
            if (!$agreementID || strlen($agreementID) < 20) {
                \Log::error('Invalid agreementID in response', [
                    'response' => $response,
                    'agreementID' => $agreementID,
                    'agreementID_length' => $agreementID ? strlen($agreementID) : 0,
                ]);
                abort(500, 'Invalid agreement ID received from bKash. Length: ' . ($agreementID ? strlen($agreementID) : 0));
            }
            
            // Get payerAccount or payerReference (bKash may return either)
            $payerAccount = $response['payerAccount'] ?? $response['payerReference'] ?? $wallet->payer_reference;
            
            //  Update wallet with final agreementID (will be encrypted at rest)
            // This ID is the master key for future charges
            $wallet->update([
                'agreement_id' => $agreementID, // Store final agreementId
                'token' => $agreementID, // Encrypted automatically by setTokenAttribute
                'masked' => $payerAccount,
                'agreement_status' => $response['agreementStatus'] ?? 'Completed',
            ]);
            
            // Verify the stored token (after encryption/decryption)
            $storedToken = $wallet->fresh()->token; // Get fresh instance and decrypt
            \Log::info('Wallet updated successfully - Final agreement stored and encrypted', [
                'wallet_id' => $wallet->id,
                'original_agreement_id_length' => strlen($agreementID),
                'stored_token_length' => strlen($storedToken),
                'tokens_match' => $storedToken === $agreementID,
                'payer_account' => $payerAccount,
                'agreement_status' => $wallet->agreement_status,
            ]);

            // Clear cache
            Cache::forget('agreement_user_' . $request->paymentID);

            // Return HTML page that closes popup and redirects to wallet dashboard
            // This will be shown in the popup window opened by bKash
            $redirectUrl = url('/wallet');
            
            return response()->view('bkash.callback-success', [
                'message' => 'Wallet bound successfully',
                'redirectUrl' => $redirectUrl,
                'wallet' => $wallet
            ]);
        });
    }

    /**
     * Handle bKash payment callback (for top-up)
     */
    public function paymentCallback(Request $request, BkashService $bkash)
    {
        if ($request->status !== 'success') {
            return response()->json(['message' => 'Payment cancelled'], 400);
        }

        return DB::transaction(function () use ($request, $bkash) {
            // 1️⃣ Lock transaction
            $trx = Transaction::where('payment_id', $request->paymentID)
                ->lockForUpdate()
                ->firstOrFail();

            if ($trx->status === 'success') {
                return response()->json(['message' => 'Already processed']);
            }

            // 2️⃣ Execute payment
            $response = $bkash->executePayment($request->paymentID);

            if (($response['statusCode'] ?? null) !== '0000') {
                $trx->update(['status' => 'failed']);
                abort(500, 'Payment execution failed');
            }

            // 3️⃣ Lock wallet
            $wallet = Wallet::where('id', $trx->wallet_id)
                ->lockForUpdate()
                ->first();

            // 4️⃣ Update balance
            $wallet->balance += $trx->amount;
            $wallet->save();

            // 5️⃣ Mark transaction success
            $trx->update([
                'status' => 'success',
                'trx_id' => $response['trxID']
            ]);

            return response()->json([
                'message' => 'Top-up successful',
                'balance' => $wallet->balance
            ]);
        });
    }
}
