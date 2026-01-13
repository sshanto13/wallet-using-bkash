<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('dashboard');
Route::get('/wallet', function () {
    return view('wallet'); // Blade file we'll create next
})->name('wallet.dashboard');