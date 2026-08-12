<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

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
        $req = Http::timeout($this->timeout)->acceptJson();
        if ($this->proKey && str_contains($this->baseUrl, 'pro-api.coingecko.com')) {
            $req = $req->withHeaders(['x-cg-pro-api-key' => $this->proKey]);
        }
        return $req;
    }

    private function assertOk($resp, string $context): array
    {
        if (!$resp->successful()) {
            throw new RuntimeException("CoinGecko {$context}: HTTP {$resp->status()}");
        }
        $json = $resp->json();
        if (!is_array($json)) {
            throw new RuntimeException("CoinGecko {$context}: invalid JSON");
        }
        // Rate-limit / error envelope
        if (isset($json['status']['error_code'])) {
            $code = $json['status']['error_code'];
            $msg  = $json['status']['error_message'] ?? 'error';
            throw new RuntimeException("CoinGecko {$context}: error {$code} — {$msg}");
        }
        return $json;
    }

    /** 1) Prix simple: /simple/price */
    public function getSimplePrice(array $ids, array $vsCurrencies = ['usd']): array
    {
        $resp = $this->client()->get("{$this->baseUrl}/simple/price", [
            'ids' => implode(',', $ids),
            'vs_currencies' => implode(',', $vsCurrencies),
        ]);
        return $this->assertOk($resp, 'simple/price');
    }

    /** 2) Trending search: /search/trending */
    public function getTrending(): array
    {
        $resp = $this->client()->get("{$this->baseUrl}/search/trending");
        return $this->assertOk($resp, 'trending');
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
        $json = $this->assertOk($resp, 'markets');
        if (!array_is_list($json)) {
            throw new RuntimeException('CoinGecko markets: expected list');
        }
        return $json;
    }

    /** 4) OHLC (bougies): /coins/{id}/ohlc */
    public function getOHLC(string $id, string $vsCurrency = 'usd', int $days = 1): array
    {
        $resp = $this->client()->get("{$this->baseUrl}/coins/{$id}/ohlc", [
            'vs_currency' => $vsCurrency,
            'days' => $days,
        ]);
        return $this->assertOk($resp, "ohlc/{$id}");
    }

    /** 5) Market Chart: /coins/{id}/market_chart */
    public function getMarketChart(string $id, string $vsCurrency = 'usd', int $days = 30): array
    {
        $resp = $this->client()->get("{$this->baseUrl}/coins/{$id}/market_chart", [
            'vs_currency' => $vsCurrency,
            'days'        => $days,
        ]);
        return $this->assertOk($resp, "market_chart/{$id}");
    }
}
