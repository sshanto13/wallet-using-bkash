<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BkashService
{
    protected string $baseUrl;
    protected string $checkoutBaseUrl;
    protected string $appKey;
    protected string $appSecret;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl        = config('services.bkash.base_url', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta');
        $this->checkoutBaseUrl = config('services.bkash.checkout_base_url', 'https://checkout.sandbox.bka.sh/v1.2.0-beta');
        $this->appKey         = config('services.bkash.app_key');
        $this->appSecret      = config('services.bkash.app_secret');
        $this->username       = config('services.bkash.username');
        $this->password       = config('services.bkash.password');
    }

    /* ----------------------------------
     | ACCESS TOKEN
     |----------------------------------*/
    public function getAccessToken(): string
    {
        return Cache::remember('bkash_token', 3500, function () {
            $response = Http::withHeaders([
                'username' => $this->username,
                'password' => $this->password,
            ])->post($this->baseUrl . '/tokenized/checkout/token/grant', [
                'app_key'    => $this->appKey,
                'app_secret' => $this->appSecret,
            ]);

            if (!$response->successful()) {
                Log::error('bKash token grant failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('bKash token error: ' . $response->body());
            }

            $token = $response['id_token'];
            Log::info('bKash token obtained successfully');
            return $token;
        });
    }

    protected function headers(): array
    {
        return [
            'Authorization' => $this->getAccessToken(), // Capitalized as per Postman
            'X-APP-Key'      => $this->appKey, // Capitalized as per Postman
            'Content-Type'   => 'application/json',
        ];
    }

    /* ----------------------------------
     | AGREEMENT
     |----------------------------------*/
    public function createAgreement(?string $callbackURL = null, ?string $payerReference = null): array
    {
        // Use phone number from user or default
        $payerRef = $payerReference ?? '01770618575'; // Default sandbox number from Postman
        
        $payload = [
            'mode'          => '0000',
            'payerReference' => $payerRef,
        ];
        
        // Add callbackURL if provided (use demo URL for sandbox as per Postman)
        if ($callbackURL) {
            $payload['callbackURL'] = $callbackURL;
        } else {
            // Use demo callback URL as per Postman collection
            $payload['callbackURL'] = 'https://merchantdemo.sandbox.bka.sh/callback?version=v2&product=tokenized-checkout&isAgreement=true&hasAgreement=false';
        }
        
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/tokenized/checkout/create', $payload);
            
        Log::info('bKash createAgreement request', [
            'url' => $this->baseUrl . '/tokenized/checkout/create',
            'payload' => $payload,
            'status' => $response->status(),
            'response' => $response->json(),
        ]);
        
        if (!$response->successful()) {
            Log::error('bKash createAgreement failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
        
        return $response->json();
    }

    public function executeAgreement(string $paymentID): array
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/tokenized/checkout/execute', [
                'paymentID' => $paymentID,
            ]);
            
        Log::info('bKash executeAgreement request', [
            'paymentID' => $paymentID,
            'status' => $response->status(),
            'response' => $response->json(),
        ]);
        
        return $response->json();
    }

    /* ----------------------------------
     | PAYMENT
     |----------------------------------*/
    public function createPayment(
        string $agreementId,
        float $amount,
        string $invoice,
        ?string $callbackURL = null,
        ?string $payerReference = null
    ): array {
        // Use phone number from user or default sandbox number
        $payerRef = $payerReference ?? '01770618575';
        
        $payload = [
            'agreementID' => $agreementId, // bKash API uses agreementID (all caps) in responses
            'mode'        => '0011', // Mode 0011 for tokenized checkout payment with agreement
            'payerReference' => $payerRef,
            'amount'      => number_format($amount, 2, '.', ''),
            'currency'    => 'BDT',
            'intent'      => 'sale',
            'merchantInvoiceNumber' => $invoice,
        ];

        

        // Callback URL is required for payment creation (even if not used)
        // Use demo callback URL for sandbox if not provided
        if ($callbackURL) {
            $payload['callbackURL'] = $callbackURL;
        } else {
            $payload['callbackURL'] = 'https://merchantdemo.sandbox.bka.sh/callback?version=v2&product=tokenized-checkout&isAgreement=false&hasAgreement=true';
        }

        // Use tokenized endpoint for payment creation with agreement ID
        // For tokenized checkout flow, both agreements and payments use the same tokenized endpoint
        // The checkout endpoint requires AWS Signature V4, but tokenized endpoint uses simple token auth
        $paymentUrl = $this->baseUrl . '/tokenized/checkout/create';
        $response = Http::withHeaders($this->headers())
            ->post($paymentUrl, $payload);
            
        Log::info('bKash createPayment request', [
            'url' => $paymentUrl,
            'payload' => $payload,
            'agreement_id' => $agreementId,
            'status' => $response->status(),
            'response_body' => $response->body(),
        ]);

        if (!$response->successful()) {
            Log::error('bKash createPayment HTTP error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->json();
    }

    public function executePayment(string $paymentID): array
    {
        // Use tokenized endpoint for payment execution (same as payment creation)
        // For tokenized checkout flow, use tokenized endpoint with token-based auth
        $executeUrl = $this->baseUrl . '/tokenized/checkout/execute';
        $response = Http::withHeaders($this->headers())
            ->post($executeUrl, [
                'paymentID' => $paymentID,
            ]);
            
        Log::info('bKash executePayment request', [
            'url' => $executeUrl,
            'paymentID' => $paymentID,
            'status' => $response->status(),
            'response' => $response->json(),
        ]);
        
        return $response->json();
    }

    public function queryPayment(string $paymentID): array
    {
        // Use POST method as per bKash API (payment status endpoint uses POST, not GET)
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/tokenized/checkout/payment/status', [
                'paymentID' => $paymentID,
            ]);
            
        Log::info('bKash queryPayment request', [
            'url' => $this->baseUrl . '/tokenized/checkout/payment/status',
            'paymentID' => $paymentID,
            'status' => $response->status(),
            'response' => $response->json(),
        ]);
        
        return $response->json();
    }

    /* ----------------------------------
     | REFUND
     |----------------------------------*/
    public function refund(
        string $paymentID,
        float $amount,
        string $trxId,
        string $reason = 'Refund'
    ): array {
        return Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/tokenized/checkout/refund', [
                'paymentID' => $paymentID,
                'amount'    => number_format($amount, 2, '.', ''),
                'trxID'     => $trxId,
                'reason'    => $reason,
            ])->json();
    }
}
