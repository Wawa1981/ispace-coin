<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\OnchainWithdraw;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class WithdrawOnChain implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $asset,
        public string $amount,
        public string $to
    ) {}

    public function handle(): void
    {
        OnchainWithdraw::create([
            'user_id' => $this->user->id,
            'asset' => $this->asset,
            'amount' => $this->amount,
            'to_address' => $this->to,
            'status' => 'pending',
        ]);
    }
}
