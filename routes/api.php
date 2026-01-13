<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\WalletController;
use App\Http\Controllers\api\BkashCallbackController;
use App\Http\Controllers\api\LanguageController;

Route::prefix('v1')->group(function () {
    // Language routes (public)
    Route::get('/language/{lang}', [LanguageController::class, 'getTranslations']);
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // wallet top up
    // USER
  
    
    // bKash callbacks
    Route::get('/bkash/agreement/callback',[BkashCallbackController::class, 'agreementCallback'])->name('bkash.agreement.callback');
    Route::get('/bkash/payment/callback',[BkashCallbackController::class, 'paymentCallback'])->name('bkash.payment.callback');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/wallet/bind', [WalletController::class, 'bind']);
        Route::get('/wallet', [WalletController::class, 'me']);
        Route::post('/wallet/topup', [WalletController::class, 'topUp']);
        Route::post('/wallet/payment/check', [WalletController::class, 'checkPaymentStatus']);
        Route::post('/wallet/refund', [WalletController::class, 'refund']);
        Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
        Route::get('/wallet/statement', [WalletController::class, 'generate']);
        Route::post('/language/preference', [LanguageController::class, 'savePreference']);
        Route::get('/language/preference', [LanguageController::class, 'getPreference']);
    
    });
});