<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\OnchainAddress;
use App\Models\OnchainDeposit;
use App\Models\OnchainWithdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OnchainController extends Controller
{
    private function bitgoConfig(string $asset): array
    {
        $asset = strtoupper($asset);

        $configs = [
            'BTC' => [
                'coin' => env('BITGO_COIN_BTC', 'tbtc'),
                'wallet_id' => env('BITGO_WALLET_ID_TBTC') ?: env('BITGO_WALLET_ID_BTC') ?: env('BITGO_WALLET_ID'),
            ],
            'ETH' => [
                'coin' => env('BITGO_COIN_ETH', 'teth'),
                'wallet_id' => env('BITGO_WALLET_ID_ETH') ?: env('BITGO_WALLET_ID'),
            ],
            'USDT' => [
                'coin' => env('BITGO_COIN_USDT', 'teth'),
                'wallet_id' => env('BITGO_WALLET_ID_USDT') ?: env('BITGO_WALLET_ID_ETH') ?: env('BITGO_WALLET_ID'),
            ],
        ];

        if (!isset($configs[$asset])) {
            throw new \Exception('Crypto non supportée.');
        }

        return $configs[$asset];
    }

    public function depositAddress(Request $r, string $asset)
    {
        $user = $r->user();
        $asset = strtoupper($asset);

        try {
            $addr = OnchainAddress::where([
                'user_id' => $user->id,
                'asset' => $asset,
            ])->value('address');

            if (!$addr) {
                $baseUrl = rtrim(config('crypto-wallet.drivers.bitgo.express_api_url'), '/') . '/';
                $token = env('BITGO_API_KEY') ?: env('BITGO_ACCESS_TOKEN');

                $bitgo = $this->bitgoConfig($asset);

                $coin = $bitgo['coin'];
                $walletId = $bitgo['wallet_id'];

                if (!$baseUrl || !$token || !$walletId) {
                    return response()->json([
                        'success' => false,
                        'message' => "Configuration BitGo manquante pour {$asset}.",
                    ], 500);
                }

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                ])->post("{$baseUrl}{$coin}/wallet/{$walletId}/address", [
                    'label' => "user_{$user->id}_{$asset}",
                ]);

                if ($response->failed()) {
                    Log::error('Erreur BitGo création adresse: ' . $response->body());

                    return response()->json([
                        'success' => false,
                        'message' => "Erreur création adresse dépôt {$asset}.",
                        'error' => $response->json(),
                    ], 500);
                }

                $data = $response->json();
                $addr = $data['address'] ?? null;

                if (!$addr) {
                    return response()->json([
                        'success' => false,
                        'message' => 'BitGo n’a pas renvoyé d’adresse.',
                    ], 500);
                }

                OnchainAddress::create([
                    'user_id' => $user->id,
                    'asset' => $asset,
                    'address' => $addr,
                ]);
            }

            return response()->json([
                'success' => true,
                'asset' => $asset,
                'address' => $addr,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur depositAddress: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function withdraw(Request $r, string $asset)
{
    $asset = strtoupper($asset);

    $r->validate([
        'amount' => 'required|numeric|min:0.00000001',
        'to' => 'required|string',
    ]);

    if (!in_array($asset, ['BTC', 'ETH', 'USDT'], true)) {
        return response()->json([
            'success' => false,
            'message' => 'Crypto non supportée.',
        ], 422);
    }

    try {
        $fromUser = $r->user();
        $amount = (float) $r->input('amount');
        $toAddress = trim((string) $r->input('to'));

        DB::transaction(function () use ($fromUser, $asset, $amount, $toAddress) {
            $fromBalance = Balance::where('user_id', $fromUser->id)
                ->where('currency', $asset)
                ->lockForUpdate()
                ->first();

            if (!$fromBalance || (float) $fromBalance->amount < $amount) {
                throw new \Exception('Solde insuffisant.');
            }

            $internalAddress = OnchainAddress::where('asset', $asset)
                ->where('address', $toAddress)
                ->first();

            $fromBalance->amount = (float) $fromBalance->amount - $amount;
            $fromBalance->save();

            if ($internalAddress) {
                if ((int) $internalAddress->user_id === (int) $fromUser->id) {
                    throw new \Exception('Impossible de vous envoyer à vous-même.');
                }

                $toBalance = Balance::firstOrCreate(
                    [
                        'user_id' => $internalAddress->user_id,
                        'currency' => $asset,
                    ],
                    [
                        'amount' => 0,
                    ]
                );

                $toBalance = Balance::where('id', $toBalance->id)
                    ->lockForUpdate()
                    ->first();

                $toBalance->amount = (float) $toBalance->amount + $amount;
                $toBalance->save();

                OnchainWithdraw::create([
                    'user_id' => $fromUser->id,
                    'asset' => $asset,
                    'amount' => $amount,
                    'to_address' => $toAddress,
                    'status' => 'internal_transfer',
                    'meta' => [
                        'type' => 'internal',
                        'to_user_id' => $internalAddress->user_id,
                    ],
                ]);

                return;
            }

            OnchainWithdraw::create([
                'user_id' => $fromUser->id,
                'asset' => $asset,
                'amount' => $amount,
                'to_address' => $toAddress,
                'status' => 'pending',
                'meta' => [
                    'type' => 'external',
                    'source' => 'manual_withdraw',
                ],
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Transfert {$amount} {$asset} enregistré.",
            'crypto_balances' => Balance::where('user_id', $fromUser->id)
                ->get(['currency', 'amount']),
        ]);

    } catch (\Exception $e) {
        Log::error('Erreur retrait onchain: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function transactions(Request $r, string $asset)
{
    $uid = $r->user()->id;
    $asset = strtoupper($asset);

    $withdrawsOut = OnchainWithdraw::where('user_id', $uid)
        ->where('asset', $asset)
        ->latest()
        ->limit(50)
        ->get();

    $withdrawsIn = OnchainWithdraw::where('asset', $asset)
        ->where('status', 'internal_transfer')
        ->whereJsonContains('meta->to_user_id', $uid)
        ->latest()
        ->limit(50)
        ->get()
        ->map(function ($tx) {
            $tx->type_for_display = 'internal_receive';
            return $tx;
        });

    return response()->json([
        'success' => true,

        'deposits' => OnchainDeposit::where([
            'user_id' => $uid,
            'asset' => $asset,
        ])->latest()->limit(50)->get(),

        'withdraws' => $withdrawsOut,

        'incoming' => $withdrawsIn,
    ]);
}
}