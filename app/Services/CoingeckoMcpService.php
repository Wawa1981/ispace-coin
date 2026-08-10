<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

final class CoinGeckoRestService
{
    private string $baseUrl;
    private ?string $proKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.coingecko_rest.base_url'), '/');
        $this->proKey  = config('services.coingecko_rest.pro_api_key');
        $this->timeout = (int) config('services.coingecko_rest.timeout', 15);
    }

    private function client()
    {
        $req = Http::timeout($this->timeout);
        if ($this->proKey && str_contains($this->baseUrl, 'pro-api.coingecko.com')) {
            $req = $req->withHeaders(['x-cg-pro-api-key' => $this->proKey]); // Auth Pro
        }
        return $req;
    }

    /** 1) Prix simple: /simple/price */
    public function getSimplePrice(array $ids, array $vsCurrencies = ['usd']): array
    {
        $resp = $this->client()->get("{$this->baseUrl}/simple/price", [
            'ids' => implode(',', $ids),
            'vs_currencies' => implode(',', $vsCurrencies),
            // options utiles: include_market_cap, include_24hr_vol, include_24hr_change
        ]);
        return $resp->json() ?? [];
    }

    /** 2) Trending search: /search/trending */
    public function getTrending(): array
    {
        $resp = $this->client()->get("{$this->baseUrl}/search/trending");
        return $resp->json() ?? [];
    }

    /** 3) Markets list: /coins/markets */
    public function getMarkets(string $vsCurrency = 'usd', int $perPage = 10, int $page = 1, string $order = 'market_cap_desc'): array
    {
        $resp = $this->client()->get("{$this->baseUrl}/coins/markets", [
            'vs_currency' => $vsCurrency,
            'order' => $order,
            'per_page' => $perPage,
            'page' => $page,
            'sparkline' => 'false',
            'price_change_percentage' => '1h,24h,7d',
        ]);
        return $resp->json() ?? [];
    }

    /** 4) OHLC (bougies): /coins/{id}/ohlc */
    public function getOHLC(string $id, string $vsCurrency = 'usd', int $days = 1): array
    {
        // days: 1, 7, 14, 30, 90, 180, 365, max
        $resp = $this->client()->get("{$this->baseUrl}/coins/{$id}/ohlc", [
            'vs_currency' => $vsCurrency,
            'days' => $days,
        ]);
        return $resp->json() ?? [];
    }
}
