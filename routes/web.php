<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\OnchainController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Hello'))->name('home');

Route::get('/crypto', fn () => Inertia::render('Crypto'))->name('crypto');

Route::get('/crypto/{id}', function (string $id) {
    return Inertia::render('CryptoShow', [
        'coinId' => $id,
    ]);
})->name('crypto.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/deposit', fn () => Inertia::render('Deposit'))->name('deposit');
    Route::get('/achat', fn () => Inertia::render('Achat'))->name('achat');
    Route::get('/retrait', fn () => Inertia::render('Retrait'))->name('retrait');
    Route::get('/envoyer', fn () => Inertia::render('Envoyer'))->name('envoyer');
    Route::get('/echange', fn () => Inertia::render('Echange'))->name('echange');
    Route::get('/compte', fn () => Inertia::render('Compte'))->name('compte');
    Route::get('/cartes', fn () => Inertia::render('Cartes'))->name('cartes');

    Route::get('/deposit/select', fn () => Inertia::render('SelectCrypto'))->name('deposit.select');
    Route::get('/wallets/{asset}/deposit-address', [\App\Http\Controllers\OnchainController::class, 'depositAddress'])->name('wallets.depositAddress');
    Route::post('/wallets/{asset}/withdraw', [\App\Http\Controllers\OnchainController::class, 'withdraw'])->name('wallets.withdraw');
    Route::get('/wallets/{asset}/transactions', [\App\Http\Controllers\OnchainController::class, 'transactions'])->name('wallets.transactions');

    Route::get('/deposit/confirm/{symbol}', function ($symbol) {
        return Inertia::render('ConfirmDeposit', [
            'symbol' => $symbol,
        ]);
    })->name('deposit.confirm');

    Route::get('/wallet', [WalletController::class, 'balance'])->name('wallet');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');

    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/wallet/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer');
    Route::post('/wallet/exchange', [WalletController::class, 'exchange'])->name('wallet.exchange');
    Route::post('/wallet/transfer-asset', [WalletController::class, 'transferAsset'])->name('wallet.transferAsset');

    Route::get('/wallets/{asset}/deposit-address', [OnchainController::class, 'depositAddress'])->name('wallets.depositAddress');
    Route::post('/wallets/{asset}/withdraw', [OnchainController::class, 'withdraw'])->name('wallets.withdraw');
    Route::get('/wallets/{asset}/transactions', [OnchainController::class, 'transactions'])->name('wallets.transactions');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
