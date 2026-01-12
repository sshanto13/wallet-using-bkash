<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BkashService
{
    protected string $baseUrl;
    protected string $appKey;
    protected string $appSecret;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl   = config('services.bkash.base_url');
        $this->appKey    = config('services.bkash.app_key');
        $this->appSecret = config('services.bkash.app_secret');
        $this->username  = config('services.bkash.username');
        $this->password  = config('services.bkash.password');
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
                throw new \Exception('bKash token error: ' . $response->body());
            }

            return $response['id_token'];
        });
    }

    protected function headers(): array
    {
        return [
            'Authorization' => $this->getAccessToken(),
            'X-APP-Key'     => $this->appKey,
            'Content-Type'  => 'application/json',
        ];
    }

    /* ----------------------------------
     | AGREEMENT
     |----------------------------------*/
    public function createAgreement(string $callbackURL): array
    {
        return Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/tokenized/checkout/create', [
                'mode'        => '0000',
                'payerReference' => 'wallet-user',
                'callbackURL' => $callbackURL,
            ])->json();
    }

    public function executeAgreement(string $paymentID): array
    {
        return Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/tokenized/checkout/execute', [
                'paymentID' => $paymentID,
            ])->json();
    }

    /* ----------------------------------
     | PAYMENT
     |----------------------------------*/
    public function createPayment(
        string $agreementId,
        float $amount,
        string $invoice
    ): array {
        return Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/tokenized/checkout/create', [
                'agreementID' => $agreementId,
                'mode'        => '0001',
                'amount'      => number_format($amount, 2, '.', ''),
                'currency'    => 'BDT',
                'intent'      => 'sale',
                'merchantInvoiceNumber' => $invoice,
            ])->json();
    }

    public function executePayment(string $paymentID): array
    {
        return Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/tokenized/checkout/execute', [
                'paymentID' => $paymentID,
            ])->json();
    }

    public function queryPayment(string $paymentID): array
    {
        return Http::withHeaders($this->headers())
            ->get($this->baseUrl . '/tokenized/checkout/payment/status', [
                'paymentID' => $paymentID,
            ])->json();
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
