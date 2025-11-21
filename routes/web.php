<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Wallet', [
        'commissionRate' => config('wallet.commission_rate'),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
