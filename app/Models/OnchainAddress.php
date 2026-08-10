<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnchainAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
