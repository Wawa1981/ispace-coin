<?php

namespace App\Models;

use App\Models\LedgerEntry;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet as WalletInterface;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword, WalletInterface
{
    use HasApiTokens, HasFactory, Notifiable, HasWallet;

    /**
     * Champs autorisés à l’insertion / mise à jour
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'rate_limit',
        'last_login_at',
        'invite_code',
        'blocked',
        'language',
    ];

    /**
     * Champs masqués dans les réponses JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
        'g2fa_secret',
        'last_ip',
    ];

    /**
     * Casts automatiques
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relation : Tokens Sanctum
     */
    public function tokens()
    {
        return $this->morphMany(\Laravel\Sanctum\PersonalAccessToken::class, 'tokenable');
    }

    /**
     * Helpers solde
     */
    public function getBalanceCentsAttribute(): int
    {
        return $this->balanceInt;
    }

    public function getBalanceFormattedAttribute(): string
    {
        return number_format($this->balanceInt / 100, 2, ',', ' ') . ' €';
    }

    public function getBalanceFloatAttribute(): float
    {
        return $this->balanceInt / 100;
    }

    /**
     * Dépôt + écriture ledger
     */
    public function depositWithLedger(int $amount, array $meta = [])
    {
        $tx = $this->deposit($amount, $meta);

        LedgerEntry::create([
            'wallet_id'      => $tx->wallet_id,
            'transaction_id' => $tx->id,
            'transfer_id'    => null,
            'direction'      => 'credit',
            'amount'         => $amount,
            'ref'            => $meta['ref'] ?? null,
            'meta'           => $meta,
            'occurred_at'    => now(),
        ]);

        return $tx;
    }

    /**
     * Transfert + écriture ledger
     */
    public function transferWithLedger(User $to, int $amount, array $meta = [])
    {
        $tf = $this->transfer($to->wallet, $amount, $meta);

        LedgerEntry::create([
            'wallet_id'      => $tf->from_id,
            'transaction_id' => null,
            'transfer_id'    => $tf->id,
            'direction'      => 'transfer_out',
            'amount'         => $amount,
            'ref'            => $meta['ref'] ?? null,
            'meta'           => $meta,
            'occurred_at'    => now(),
        ]);

        LedgerEntry::create([
            'wallet_id'      => $tf->to_id,
            'transaction_id' => null,
            'transfer_id'    => $tf->id,
            'direction'      => 'transfer_in',
            'amount'         => $amount,
            'ref'            => $meta['ref'] ?? null,
            'meta'           => $meta,
            'occurred_at'    => now(),
        ]);

        return $tf;
    }
    public function balances()
{ 
    return $this->hasMany(\App\Models\Balance::class);
}

}
