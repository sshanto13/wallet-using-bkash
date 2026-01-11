<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BkashService;
class WalletController extends Controller
{
    /**
     * Bind bKash wallet (Agreement)
     */
    public function bind(Request $request, BkashService $bkash)
    {
        $user = $request->user();

        if ($user->wallet) {
            return response()->json(['message' => 'Wallet already bound'], 409);
        }

        // Get Access Token
        $bkash->getAccessToken();

        // Create Agreement (Sandbox)
        $maskedPhone = '019XXXXXXX'; // can be dynamic
        $agreement = $bkash->createAgreement($maskedPhone);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'token' => $agreement['agreementId'] ?? 'AGMT_'.uniqid(),
            'masked' => $agreement['customerMsisdn'] ?? $maskedPhone,
            'balance' => 0,
        ]);

        return response()->json(['wallet' => $wallet]);
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