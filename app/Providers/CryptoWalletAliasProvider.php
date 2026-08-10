<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RedberryProducts\CryptoWallet\WalletManager;

class CryptoWalletAliasProvider extends ServiceProvider
{
    public function register()
    {
        // On lie le service 'crypto-wallet' au WalletManager du package
        $this->app->singleton('crypto-wallet', function ($app) {
            return $app->make(WalletManager::class);
        });
    }

    public function boot()
    {
        //
    }
}
