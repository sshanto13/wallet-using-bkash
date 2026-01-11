<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Bind bKash wallet (Agreement)
     */
    public function bind(Request $request)
    {
        $user = $request->user();

        return DB::transaction(function () use ($user) {

            // Lock to prevent race condition
            if (
                Wallet::where('user_id', $user->id)
                    ->lockForUpdate()
                    ->exists()
            ) {
                return response()->json([
                    'message' => 'Wallet already bound'
                ], 409);
            }

            /**
             * Call bKash Agreement API here
             * Must return:
             * - agreementId
             * - masked msisdn
             */

            // TEMP MOCK
            $agreementId = 'AGMT_' . uniqid();
            $masked = '019****123';

            $wallet = Wallet::create([
                'user_id' => $user->id,
                'token'   => $agreementId,
                'masked' => $masked,
                'balance' => 0,
            ]);

            return response()->json([
                'wallet' => $wallet
            ], 201);
        });
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
}