<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\WalletController;
use App\Http\Controllers\api\BkashCallbackController;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // wallet top up
    // USER
  
    
    // call ;back 
    Route::get('/bkash/agreement/callback',[BkashCallbackController::class, 'agreementCallback'])->name('bkash.agreement.callback');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/wallet/bind', [WalletController::class, 'bind']);
        Route::get('/wallet', [WalletController::class, 'me']);
        Route::post('/wallet/topup', [WalletController::class, 'topUp']);
        Route::post('/wallet/refund', [WalletController::class, 'refund']);
        Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    });
});