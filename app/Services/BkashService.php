<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BkashService
{
    protected $baseUrl;
    protected $appKey;
    protected $appSecret;
    protected $username;
    protected $password;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = env('BKASH_SANDBOX_BASE_URL');
        $this->appKey = env('BKASH_SANDBOX_APP_KEY');
        $this->appSecret = env('BKASH_SANDBOX_APP_SECRET');
        $this->username = env('BKASH_SANDBOX_USERNAME');
        $this->password = env('BKASH_SANDBOX_PASSWORD');
    }

    // Step 1: Get Access Token
    public function getAccessToken()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'username' => $this->username,
            'password' => $this->password,
        ])->post($this->baseUrl.'/token/grant', [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret,
        ]);

        $data = $response->json();

        if (isset($data['id_token'])) {
            $this->token = $data['id_token'];
            return $data['id_token'];
        }

        return null;
    }

    // Step 2: Create Agreement
    public function createAgreement($maskedPhone)
    {
        if (!$this->token) {
            $this->getAccessToken();
        }

        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl.'/create-agreement', [
            'mode' => '0011', // sandbox
            'payerReference' => $maskedPhone,
            'callbackURL' => 'http://127.0.0.1:8000/api/v1/wallet/bkash-callback',
        ]);

        return $response->json();
    }

    // Step 3: Execute Payment (for future)
    public function executePayment($agreementId, $amount)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl.'/execute-payment', [
            'agreementId' => $agreementId,
            'amount' => $amount,
            'currency' => 'BDT',
        ]);

        return $response->json();
    }
}
