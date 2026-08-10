<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnchainWithdraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset',
        'amount',
        'to_address',
        'txid',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}