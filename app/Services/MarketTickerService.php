<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Bandeau multi-marchés: crypto + devises fiat + actions.
 * Chaque bloc a son cache / last-good indépendant pour éviter qu'un panne
 * d'un segment n'efface les autres.
 */
final class MarketTickerService
{
    private const FRESH_TTL = 90;
    private const LAST_GOOD_TTL = 86400;
    private const HTTP_TIMEOUT = 8;

    /** Paires forex affichées (Yahoo: EURUSD=X) */
    private const FX_PAIRS = [
        'EURUSD=X' => ['id' => 'eurusd', 'name' => 'EUR/USD', 'symbol' => 'EURUSD'],
        'EURGBP=X' => ['id' => 'eurgbp', 'name' => 'EUR/GBP', 'symbol' => 'EURGBP'],
        'EURCHF=X' => ['id' => 'eurchf', 'name' => 'EUR/CHF', 'symbol' => 'EURCHF'],
        'EURJPY=X' => ['id' => 'eurjpy', 'name' => 'EUR/JPY', 'symbol' => 'EURJPY'],
        'GBPUSD=X' => ['id' => 'gbpusd', 'name' => 'GBP/USD', 'symbol' => 'GBPUSD'],
        'USDJPY=X' => ['id' => 'usdjpy', 'name' => 'USD/JPY', 'symbol' => 'USDJPY'],
        'USDCAD=X' => ['id' => 'usdcad', 'name' => 'USD/CAD', 'symbol' => 'USDCAD'],
        'USDCHF=X' => ['id' => 'usdchf', 'name' => 'USD/CHF', 'symbol' => 'USDCHF'],
    ];

    /** Actions en vogue (US + quelques européennes) */
    private const STOCKS = [
        'AAPL'  => ['name' => 'Apple', 'currency' => 'USD'],
        'MSFT'  => ['name' => 'Microsoft', 'currency' => 'USD'],
        'NVDA'  => ['name' => 'NVIDIA', 'currency' => 'USD'],
        'TSLA'  => ['name' => 'Tesla', 'currency' => 'USD'],
        'AMZN'  => ['name' => 'Amazon', 'currency' => 'USD'],
        'GOOGL' => ['name' => 'Alphabet', 'currency' => 'USD'],
        'META'  => ['name' => 'Meta', 'currency' => 'USD'],
        'AMD'   => ['name' => 'AMD', 'currency' => 'USD'],
        'NFLX'  => ['name' => 'Netflix', 'currency' => 'USD'],
        'JPM'   => ['name' => 'JPMorgan', 'currency' => 'USD'],
        'MC.PA' => ['name' => 'LVMH', 'currency' => 'EUR'],
        'AIR.PA'=> ['name' => 'Airbus', 'currency' => 'EUR'],
        'OR.PA' => ['name' => "L'Oréal", 'currency' => 'EUR'],
        'ASML'  => ['name' => 'ASML', 'currency' => 'USD'],
    ];

    public function __construct(
        private readonly CryptoPriceService $cryptoPrices,
    ) {}

    /**
     * @return array{
     *   items: array<int, array>,
     *   stale: bool,
     *   sources: array<string, string>,
     *   fetched_at: int|null
     * }
     */
    public function getBoard(): array
    {
        $freshKey = 'ticker:board:v1';
        $lastKey  = 'ticker:board:last_good:v1';

        $cached = Cache::get($freshKey);
        if (is_array($cached) && !empty($cached['items'])) {
            return $cached;
        }

        $crypto = $this->fetchCryptoSegment();
        $fiat   = $this->fetchFiatSegment();
        $stocks = $this->fetchStocksSegment();

        $items = array_values(array_filter(array_merge(
            $crypto['items'],
            $fiat['items'],
            $stocks['items'],
        )));

        $stale = (bool) ($crypto['stale'] || $fiat['stale'] || $stocks['stale']);
        $sources = [
            'crypto' => $crypto['source'],
            'fiat'   => $fiat['source'],
            'stocks' => $stocks['source'],
        ];

        if (!empty($items)) {
            $payload = [
                'items'      => $items,
                'stale'      => $stale,
                'sources'    => $sources,
                'fetched_at' => time(),
            ];
            // Si au moins un segment a des données fraîches, on cache le board
            $allStale = $crypto['stale'] && $fiat['stale'] && $stocks['stale'];
            if (!$allStale || empty(Cache::get($lastKey))) {
                Cache::put($freshKey, $payload, self::FRESH_TTL);
                Cache::put($lastKey, $payload, self::LAST_GOOD_TTL);
            }
            return $payload;
        }

        $last = Cache::get($lastKey);
        if (is_array($last) && !empty($last['items'])) {
            $last['stale'] = true;
            return $last;
        }

        return [
            'items'      => [],
            'stale'      => true,
            'sources'    => $sources,
            'fetched_at' => null,
        ];
    }

    // ── Crypto ───────────────────────────────────────────────────────

    private function fetchCryptoSegment(): array
    {
        try {
            $result = $this->cryptoPrices->getMarkets('usd', 12, 1);
            $items = [];
            foreach ($result['data'] ?? [] as $c) {
                $items[] = [
                    'id' => $c['id'] ?? null,
                    'type' => 'crypto',
                    'name' => $c['name'] ?? null,
                    'symbol' => strtoupper((string) ($c['symbol'] ?? '')),
                    'current_price' => $c['current_price'] ?? null,
                    'price_change_percentage_24h' => $c['price_change_percentage_24h'] ?? null,
                    'currency' => 'USD',
                    'image' => $c['image'] ?? null,
                ];
            }
            return [
                'items'  => $items,
                'source' => $result['source'] ?? 'none',
                'stale'  => (bool) ($result['stale'] ?? false),
            ];
        } catch (\Throwable $e) {
            Log::warning('Ticker crypto failed: ' . $e->getMessage());
            return ['items' => [], 'source' => 'none', 'stale' => true];
        }
    }

    // ── Fiat ─────────────────────────────────────────────────────────

    private function fetchFiatSegment(): array
    {
        $freshKey = 'ticker:fiat:v1';
        $lastKey  = 'ticker:fiat:last_good:v1';

        $cached = Cache::get($freshKey);
        if (is_array($cached) && !empty($cached['items'])) {
            return $cached;
        }

        $items = $this->fetchFiatFromYahoo();
        $source = 'yahoo';

        if (empty($items)) {
            $items = $this->fetchFiatFromFrankfurter();
            $source = 'frankfurter';
        }

        if (!empty($items)) {
            $payload = ['items' => $items, 'source' => $source, 'stale' => false];
            Cache::put($freshKey, $payload, self::FRESH_TTL);
            Cache::put($lastKey, $payload, self::LAST_GOOD_TTL);
            return $payload;
        }

        $last = Cache::get($lastKey);
        if (is_array($last) && !empty($last['items'])) {
            $last['stale'] = true;
            return $last;
        }

        return ['items' => [], 'source' => 'none', 'stale' => true];
    }

    private function fetchFiatFromYahoo(): array
    {
        try {
            $symbols = array_keys(self::FX_PAIRS);
            $quotes = $this->yahooQuotes($symbols);
            $out = [];
            foreach ($symbols as $yahooSym) {
                $meta = self::FX_PAIRS[$yahooSym];
                $q = $quotes[$yahooSym] ?? null;
                if (!$q || $q['price'] === null) {
                    continue;
                }
                $out[] = [
                    'id' => $meta['id'],
                    'type' => 'fiat',
                    'name' => $meta['name'],
                    'symbol' => $meta['symbol'],
                    'current_price' => $q['price'],
                    'price_change_percentage_24h' => $q['change_pct'],
                    'currency' => null,
                    'image' => null,
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            Log::warning('Ticker fiat Yahoo failed: ' . $e->getMessage());
            return [];
        }
    }

    /** Fallback ECB (Frankfurter) — pas de % 24h */
    private function fetchFiatFromFrankfurter(): array
    {
        try {
            $resp = Http::timeout(self::HTTP_TIMEOUT)
                ->acceptJson()
                ->withOptions(['allow_redirects' => true])
                ->get('https://api.frankfurter.app/latest', [
                    'from' => 'EUR',
                    'to'   => 'USD,GBP,CHF,JPY,CAD,CNY',
                ]);

            if (!$resp->successful()) {
                throw new \RuntimeException('HTTP ' . $resp->status());
            }

            $rates = $resp->json('rates') ?? [];
            if (!is_array($rates) || empty($rates)) {
                return [];
            }

            $map = [
                'USD' => ['id' => 'eurusd', 'name' => 'EUR/USD', 'symbol' => 'EURUSD'],
                'GBP' => ['id' => 'eurgbp', 'name' => 'EUR/GBP', 'symbol' => 'EURGBP'],
                'CHF' => ['id' => 'eurchf', 'name' => 'EUR/CHF', 'symbol' => 'EURCHF'],
                'JPY' => ['id' => 'eurjpy', 'name' => 'EUR/JPY', 'symbol' => 'EURJPY'],
                'CAD' => ['id' => 'eurcad', 'name' => 'EUR/CAD', 'symbol' => 'EURCAD'],
                'CNY' => ['id' => 'eurcny', 'name' => 'EUR/CNY', 'symbol' => 'EURCNY'],
            ];

            $out = [];
            foreach ($map as $code => $meta) {
                if (!isset($rates[$code])) {
                    continue;
                }
                $out[] = [
                    'id' => $meta['id'],
                    'type' => 'fiat',
                    'name' => $meta['name'],
                    'symbol' => $meta['symbol'],
                    'current_price' => (float) $rates[$code],
                    'price_change_percentage_24h' => null,
                    'currency' => null,
                    'image' => null,
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            Log::warning('Ticker fiat Frankfurter failed: ' . $e->getMessage());
            return [];
        }
    }

    // ── Stocks ───────────────────────────────────────────────────────

    private function fetchStocksSegment(): array
    {
        $freshKey = 'ticker:stocks:v1';
        $lastKey  = 'ticker:stocks:last_good:v1';

        $cached = Cache::get($freshKey);
        if (is_array($cached) && !empty($cached['items'])) {
            return $cached;
        }

        $items = $this->fetchStocksFromYahoo();

        if (!empty($items)) {
            $payload = ['items' => $items, 'source' => 'yahoo', 'stale' => false];
            Cache::put($freshKey, $payload, self::FRESH_TTL);
            Cache::put($lastKey, $payload, self::LAST_GOOD_TTL);
            return $payload;
        }

        $last = Cache::get($lastKey);
        if (is_array($last) && !empty($last['items'])) {
            $last['stale'] = true;
            return $last;
        }

        return ['items' => [], 'source' => 'none', 'stale' => true];
    }

    private function fetchStocksFromYahoo(): array
    {
        try {
            $symbols = array_keys(self::STOCKS);
            $quotes = $this->yahooQuotes($symbols);
            $out = [];
            foreach ($symbols as $sym) {
                $meta = self::STOCKS[$sym];
                $q = $quotes[$sym] ?? null;
                if (!$q || $q['price'] === null) {
                    continue;
                }
                $out[] = [
                    'id' => strtolower(str_replace('.', '-', $sym)),
                    'type' => 'stock',
                    'name' => $meta['name'],
                    'symbol' => $sym,
                    'current_price' => $q['price'],
                    'price_change_percentage_24h' => $q['change_pct'],
                    'currency' => $meta['currency'],
                    'image' => null,
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            Log::warning('Ticker stocks Yahoo failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère des cotations Yahoo Finance en parallèle (chart endpoint, gratuit).
     *
     * @param  array<int, string>  $symbols
     * @return array<string, array{price: ?float, change_pct: ?float}>
     */
    private function yahooQuotes(array $symbols): array
    {
        $responses = Http::pool(function ($pool) use ($symbols) {
            foreach ($symbols as $sym) {
                $pool->as($sym)
                    ->timeout(self::HTTP_TIMEOUT)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; iSpaceCoin/1.0)',
                        'Accept' => 'application/json',
                    ])
                    ->get("https://query1.finance.yahoo.com/v8/finance/chart/{$sym}", [
                        'interval' => '1d',
                        'range'    => '5d',
                    ]);
            }
        });

        $out = [];
        foreach ($symbols as $sym) {
            $out[$sym] = ['price' => null, 'change_pct' => null];
            $resp = $responses[$sym] ?? null;
            if (!$resp || $resp instanceof \Throwable || !$resp->successful()) {
                continue;
            }
            $result = $resp->json('chart.result.0') ?? null;
            if (!is_array($result)) {
                continue;
            }
            $meta = $result['meta'] ?? [];
            $price = isset($meta['regularMarketPrice'])
                ? (float) $meta['regularMarketPrice']
                : null;

            $prev = isset($meta['chartPreviousClose'])
                ? (float) $meta['chartPreviousClose']
                : null;

            // Si pas de prev dans meta, prendre l'avant-dernier close
            if ($prev === null) {
                $closes = $result['indicators']['quote'][0]['close'] ?? [];
                $closes = array_values(array_filter(
                    is_array($closes) ? $closes : [],
                    fn ($v) => $v !== null
                ));
                if (count($closes) >= 2) {
                    $prev = (float) $closes[count($closes) - 2];
                    if ($price === null) {
                        $price = (float) end($closes);
                    }
                } elseif (count($closes) === 1 && $price === null) {
                    $price = (float) $closes[0];
                }
            }

            $changePct = null;
            if ($price !== null && $prev !== null && $prev != 0.0) {
                $changePct = (($price - $prev) / $prev) * 100;
            }

            $out[$sym] = [
                'price' => $price,
                'change_pct' => $changePct !== null ? round($changePct, 2) : null,
            ];
        }

        return $out;
    }
}
