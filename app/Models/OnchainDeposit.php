<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnchainDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset',
        'txid',
        'amount',
        'confirmations',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
