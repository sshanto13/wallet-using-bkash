<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\BkashService;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BkashCallbackController extends Controller
{
    public function agreementCallback1(Request $request, BkashService $bkash)
    {
        /**
         * bKash sends:
         * - paymentID
         * - status
         */

        if ($request->status !== 'success') {
            return response()->json([
                'message' => 'Agreement cancelled'
            ], 400);
        }

        return DB::transaction(function () use ($request, $bkash) {

            // 1️⃣ Execute agreement
            $response = $bkash->executeAgreement($request->paymentID);

            if (($response['statusCode'] ?? null) !== '0000') {
                abort(500, 'Agreement execution failed');
            }

            // 2️⃣ Prevent duplicate wallet
            $existing = Wallet::where('token', $response['agreementID'])->first();
            if ($existing) {
                return response()->json([
                    'message' => 'Wallet already bound',
                    'wallet' => $existing
                ]);
            }

            // 3️⃣ Save wallet
            $wallet = Wallet::create([
                'user_id' => 1, // sandbox TEMP (we bind properly later)
                'token'   => $response['agreementID'],
                'masked'  => $response['payerReference'] ?? '019****XXX',
                'balance' => 0,
            ]);

            return response()->json([
                'message' => 'Wallet bound successfully',
                'wallet'  => $wallet
            ]);
        });
    }
    
    public function agreementCallback(Request $request, BkashService $bkash)
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
