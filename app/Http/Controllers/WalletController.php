<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\OnchainWithdraw;
use App\Models\LedgerEntry;

class WalletController extends Controller
{
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $user = auth()->user();
            $amount = $request->input('amount');

            $user->depositWithLedger($amount, ['ref' => 'manual_deposit']);

            return $this->balanceResponse("Vous avez déposé {$amount}€ !");
        } catch (\Exception $e) {
            Log::error('Erreur dépôt: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du dépôt',
            ], 500);
        }
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $user = auth()->user();
            $amount = $request->input('amount');

            $user->withdrawWithLedger($amount, ['ref' => 'manual_withdraw']);

            return $this->balanceResponse("Vous avez retiré {$amount}€ !");
        } catch (\Exception $e) {
            Log::error('Erreur retrait: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait',
            ], 500);
        }
    }

    public function transactions()
{
    try {
        $user = auth()->user();

        $wallet = $user->wallet;

        $ledgerEntries = \App\Models\LedgerEntry::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'created_at' => $entry->created_at,
                    'type' => $entry->direction,
                    'amount' => $entry->amount,
                    'currency' => 'EUR',
                    'status' => 'Succès',
                    'ref' => $entry->ref,
                ];
            });

        return response()->json([
            'success' => true,
            'transactions' => $ledgerEntries,
        ]);
    } catch (\Exception $e) {
        Log::error('Erreur historique transactions: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération',
        ], 500);
    }
}
    public function transfer(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $user = auth()->user();
            $dest = User::where('email', $request->input('email'))->firstOrFail();
            $amount = $request->input('amount');

            $user->transferWithLedger($dest, $amount, ['ref' => 'manual_transfer']);

            return $this->balanceResponse("Vous avez transféré {$amount}€ à {$dest->name} !");
        } catch (\Exception $e) {
            Log::error('Erreur transfert: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du transfert',
            ], 500);
        }
    }

    public function balance()
    {
        return $this->balanceResponse();
    }

    public function exchange(Request $request)
    {
        $request->validate([
            'from' => 'required|string|in:EUR,BTC,ETH,USDT',
            'to' => 'required|string|in:EUR,BTC,ETH,USDT',
            'amount' => 'required|numeric|min:0.00000001',
            'converted_amount' => 'required|numeric|min:0.00000001',
        ]);

        try {
            $user = auth()->user();

            $from = strtoupper($request->input('from'));
            $to = strtoupper($request->input('to'));
            $amount = (float) $request->input('amount');
            $convertedAmount = (float) $request->input('converted_amount');

            if ($from === $to) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d’échanger la même devise.',
                ], 422);
            }

            DB::transaction(function () use ($user, $from, $to, $amount, $convertedAmount) {
                $this->debitUserAsset($user, $from, $amount);
                $this->creditUserAsset($user, $to, $convertedAmount);
            });

            return $this->balanceResponse(
                "Échange effectué : {$amount} {$from} → {$convertedAmount} {$to}"
            );
        } catch (\Exception $e) {
            Log::error('Erreur échange: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() === 'Solde insuffisant.'
                    ? 'Solde insuffisant.'
                    : 'Erreur lors de l’échange.',
            ], 500);
        }
    }

    public function transferAsset(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'asset' => 'required|string|in:EUR,BTC,ETH,USDT',
        'amount' => 'required|numeric|min:0.00000001',
    ]);

    try {
        $fromUser = auth()->user();
        $toUser = User::where('email', $request->input('email'))->firstOrFail();

        $asset = strtoupper($request->input('asset'));
        $amount = (float) $request->input('amount');

        Log::info('TRANSFER ASSET DEBUG', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
            'to_email' => $toUser->email,
            'asset' => $asset,
            'amount' => $amount,
        ]);

        if ($fromUser->id === $toUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de vous envoyer à vous-même.',
            ], 422);
        }

        DB::transaction(function () use ($fromUser, $toUser, $asset, $amount) {
            $this->debitUserAsset($fromUser, $asset, $amount);
            $this->creditUserAsset($toUser, $asset, $amount);

            if ($asset === 'EUR') {
                $fromWallet = $fromUser->wallet()->lockForUpdate()->first();
                $toWallet = $toUser->wallet()->lockForUpdate()->first();

                LedgerEntry::create([
                    'wallet_id' => $fromWallet->id,
                    'direction' => 'transfer_out',
                    'amount' => $amount,
                    'ref' => 'manual_transfer_asset',
                    'meta' => [
                        'type' => 'internal_email',
                        'to_user_id' => $toUser->id,
                        'to_email' => $toUser->email,
                    ],
                    'occurred_at' => now(),
                ]);

                LedgerEntry::create([
                    'wallet_id' => $toWallet->id,
                    'direction' => 'transfer_in',
                    'amount' => $amount,
                    'ref' => 'manual_transfer_asset',
                    'meta' => [
                        'type' => 'internal_email',
                        'from_user_id' => $fromUser->id,
                        'from_email' => $fromUser->email,
                    ],
                    'occurred_at' => now(),
                ]);

                return;
            }

            OnchainWithdraw::create([
                'user_id' => $fromUser->id,
                'asset' => $asset,
                'amount' => $amount,
                'to_address' => $toUser->email,
                'status' => 'internal_transfer',
                'meta' => [
                    'type' => 'internal_email',
                    'to_user_id' => $toUser->id,
                    'to_email' => $toUser->email,
                ],
            ]);
        });

        return $this->balanceResponse(
            "Vous avez envoyé {$amount} {$asset} à {$toUser->email}."
        );
    } catch (\Exception $e) {
        Log::error('Erreur transfert asset: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => $e->getMessage() === 'Solde insuffisant.'
                ? 'Solde insuffisant.'
                : 'Erreur lors du transfert.',
        ], 500);
    }
}
    private function debitUserAsset(User $user, string $asset, float $amount): void
    {
        if ($asset === 'EUR') {
            $wallet = $user->wallet()->lockForUpdate()->first();

            if (!$wallet || (float) $wallet->balance < $amount) {
                throw new \Exception('Solde insuffisant.');
            }

            $wallet->balance = (float) $wallet->balance - $amount;
            $wallet->save();

            return;
        }

        $balance = Balance::where('user_id', $user->id)
            ->where('currency', $asset)
            ->lockForUpdate()
            ->first();

        if (!$balance || (float) $balance->amount < $amount) {
            throw new \Exception('Solde insuffisant.');
        }

        $balance->amount = (float) $balance->amount - $amount;
        $balance->save();
    }

    private function creditUserAsset(User $user, string $asset, float $amount): void
    {
        if ($asset === 'EUR') {
            $wallet = $user->wallet()->lockForUpdate()->first();

            if (!$wallet) {
                $wallet = $user->wallet;
            }

            $wallet->balance = (float) $wallet->balance + $amount;
            $wallet->save();

            return;
        }

        $balance = Balance::firstOrCreate(
            [
                'user_id' => $user->id,
                'currency' => $asset,
            ],
            [
                'amount' => 0,
            ]
        );

        $balance = Balance::where('id', $balance->id)
            ->lockForUpdate()
            ->first();

        $balance->amount = (float) $balance->amount + $amount;
        $balance->save();
    }

    private function balanceResponse(?string $message = null)
    {
        $user = auth()->user();

        $user->load('wallet');

        $payload = [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'balance' => $user->wallet?->balance ?? 0,
            'crypto_balances' => Balance::where('user_id', $user->id)
                ->orderBy('currency')
                ->get(['currency', 'amount']),
        ];

        if ($message) {
            $payload['message'] = $message;
        }

        Log::info('WALLET BALANCE RESPONSE', [
            'user_id' => $user->id,
            'email' => $user->email,
            'balance' => $payload['balance'],
            'crypto_balances' => $payload['crypto_balances']->toArray(),
        ]);

        return response()->json($payload)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}