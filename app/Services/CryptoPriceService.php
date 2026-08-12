<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Prix crypto multi-source avec failover + last-good.
 *
 * Ordre: CoinGecko → CoinCap → Binance
 * - Cache frais court (évite de spammer les APIs gratuites)
 * - Last-good longue durée (jamais d'écran vide si on a déjà eu des prix)
 * - Ne met JAMAIS en cache une réponse d'erreur / rate-limit
 */
final class CryptoPriceService
{
    private const FRESH_TTL = 90;          // secondes — cache "frais"
    private const LAST_GOOD_TTL = 86400;   // 24h — garder les derniers bons prix
    private const HTTP_TIMEOUT = 8;

    /** Mapping Binance symbol → id/name (top coins) */
    private const BINANCE_MAP = [
        'BTCUSDT'  => ['id' => 'bitcoin', 'name' => 'Bitcoin', 'symbol' => 'btc'],
        'ETHUSDT'  => ['id' => 'ethereum', 'name' => 'Ethereum', 'symbol' => 'eth'],
        'BNBUSDT'  => ['id' => 'binancecoin', 'name' => 'BNB', 'symbol' => 'bnb'],
        'SOLUSDT'  => ['id' => 'solana', 'name' => 'Solana', 'symbol' => 'sol'],
        'XRPUSDT'  => ['id' => 'ripple', 'name' => 'XRP', 'symbol' => 'xrp'],
        'ADAUSDT'  => ['id' => 'cardano', 'name' => 'Cardano', 'symbol' => 'ada'],
        'DOGEUSDT' => ['id' => 'dogecoin', 'name' => 'Dogecoin', 'symbol' => 'doge'],
        'TRXUSDT'  => ['id' => 'tron', 'name' => 'TRON', 'symbol' => 'trx'],
        'TONUSDT'  => ['id' => 'the-open-network', 'name' => 'Toncoin', 'symbol' => 'ton'],
        'AVAXUSDT' => ['id' => 'avalanche-2', 'name' => 'Avalanche', 'symbol' => 'avax'],
        'SHIBUSDT' => ['id' => 'shiba-inu', 'name' => 'Shiba Inu', 'symbol' => 'shib'],
        'DOTUSDT'  => ['id' => 'polkadot', 'name' => 'Polkadot', 'symbol' => 'dot'],
        'LINKUSDT' => ['id' => 'chainlink', 'name' => 'Chainlink', 'symbol' => 'link'],
        'MATICUSDT'=> ['id' => 'matic-network', 'name' => 'Polygon', 'symbol' => 'matic'],
        'LTCUSDT'  => ['id' => 'litecoin', 'name' => 'Litecoin', 'symbol' => 'ltc'],
        'BCHUSDT'  => ['id' => 'bitcoin-cash', 'name' => 'Bitcoin Cash', 'symbol' => 'bch'],
        'UNIUSDT'  => ['id' => 'uniswap', 'name' => 'Uniswap', 'symbol' => 'uni'],
        'ATOMUSDT' => ['id' => 'cosmos', 'name' => 'Cosmos', 'symbol' => 'atom'],
        'XLMUSDT'  => ['id' => 'stellar', 'name' => 'Stellar', 'symbol' => 'xlm'],
        'NEARUSDT' => ['id' => 'near', 'name' => 'NEAR Protocol', 'symbol' => 'near'],
        'APTUSDT'  => ['id' => 'aptos', 'name' => 'Aptos', 'symbol' => 'apt'],
        'ARBUSDT'  => ['id' => 'arbitrum', 'name' => 'Arbitrum', 'symbol' => 'arb'],
        'OPUSDT'   => ['id' => 'optimism', 'name' => 'Optimism', 'symbol' => 'op'],
        'SUIUSDT'  => ['id' => 'sui', 'name' => 'Sui', 'symbol' => 'sui'],
        'PEPEUSDT' => ['id' => 'pepe', 'name' => 'Pepe', 'symbol' => 'pepe'],
    ];

    /**
     * @return array{data: array, source: string, stale: bool, fetched_at: int|null}
     */
    public function getMarkets(string $vs = 'usd', int $perPage = 100, int $page = 1): array
    {
        $freshKey = "prices:fresh:markets:{$vs}:{$perPage}:{$page}";
        $lastKey  = "prices:last_good:markets:{$vs}:{$perPage}:{$page}";

        // 1) Cache frais
        $cached = Cache::get($freshKey);
        if (is_array($cached) && !empty($cached['data'])) {
            return $cached;
        }

        // 2) Providers en cascade
        $result = $this->fetchMarketsCascade($vs, $perPage, $page);

        if ($result !== null) {
            $payload = [
                'data'       => $result['data'],
                'source'     => $result['source'],
                'stale'      => false,
                'fetched_at' => time(),
            ];
            Cache::put($freshKey, $payload, self::FRESH_TTL);
            Cache::put($lastKey, $payload, self::LAST_GOOD_TTL);
            return $payload;
        }

        // 3) Last-good (pas de coupure UI)
        $last = Cache::get($lastKey);
        if (is_array($last) && !empty($last['data'])) {
            $last['stale'] = true;
            return $last;
        }

        // 4) Total fail — tableau vide, le front garde ses données locales s'il en a
        return [
            'data'       => [],
            'source'     => 'none',
            'stale'      => true,
            'fetched_at' => null,
        ];
    }

    /**
     * Prix simple multi-ids (format CoinGecko: { bitcoin: { usd: 65000 }, ... })
     *
     * @return array{data: array, source: string, stale: bool, fetched_at: int|null}
     */
    public function getSimplePrice(array $ids, array $vsCurrencies = ['usd']): array
    {
        $ids = array_values(array_filter(array_map('trim', $ids)));
        $vs  = array_values(array_filter(array_map('trim', $vsCurrencies)));
        if (empty($ids)) {
            return ['data' => [], 'source' => 'none', 'stale' => false, 'fetched_at' => time()];
        }

        $freshKey = 'prices:fresh:simple:' . md5(json_encode([$ids, $vs]));
        $lastKey  = 'prices:last_good:simple:' . md5(json_encode([$ids, $vs]));

        $cached = Cache::get($freshKey);
        if (is_array($cached) && !empty($cached['data'])) {
            return $cached;
        }

        $result = $this->fetchSimplePriceCascade($ids, $vs);

        if ($result !== null) {
            $payload = [
                'data'       => $result['data'],
                'source'     => $result['source'],
                'stale'      => false,
                'fetched_at' => time(),
            ];
            Cache::put($freshKey, $payload, self::FRESH_TTL);
            Cache::put($lastKey, $payload, self::LAST_GOOD_TTL);
            return $payload;
        }

        $last = Cache::get($lastKey);
        if (is_array($last) && !empty($last['data'])) {
            $last['stale'] = true;
            return $last;
        }

        return ['data' => [], 'source' => 'none', 'stale' => true, 'fetched_at' => null];
    }

    // ── Cascade markets ──────────────────────────────────────────────

    private function fetchMarketsCascade(string $vs, int $perPage, int $page): ?array
    {
        // Ordre: meilleure richesse de données → fiabilité brute
        $providers = [
            'coingecko'     => fn () => $this->fromCoinGeckoMarkets($vs, $perPage, $page),
            'cryptocompare' => fn () => $this->fromCryptoCompareMarkets($perPage, $page),
            'coinlore'      => fn () => $this->fromCoinLoreMarkets($perPage, $page),
            'coincap'       => fn () => $this->fromCoinCapMarkets($perPage, $page),
            'binance'       => fn () => $this->fromBinanceMarkets($perPage),
        ];

        foreach ($providers as $name => $fn) {
            try {
                $data = $fn();
                if ($this->isValidMarketsList($data)) {
                    return ['data' => $data, 'source' => $name];
                }
            } catch (\Throwable $e) {
                Log::warning("CryptoPrice [{$name}] markets failed: " . $e->getMessage());
            }
        }

        return null;
    }

    private function fetchSimplePriceCascade(array $ids, array $vs): ?array
    {
        try {
            $data = $this->fromCoinGeckoSimple($ids, $vs);
            if (!empty($data) && $this->looksLikePriceMap($data)) {
                return ['data' => $data, 'source' => 'coingecko'];
            }
        } catch (\Throwable $e) {
            Log::warning('CryptoPrice [coingecko] simple failed: ' . $e->getMessage());
        }

        // Fallback: markets (USD only) puis filtrer
        try {
            $markets = $this->getMarkets('usd', 250, 1);
            if (!empty($markets['data'])) {
                $map = [];
                $idSet = array_flip($ids);
                foreach ($markets['data'] as $coin) {
                    $cid = $coin['id'] ?? null;
                    if ($cid && isset($idSet[$cid])) {
                        $entry = [];
                        foreach ($vs as $currency) {
                            // On n'a que USD côté fallbacks, on mappe tout sur current_price
                            $entry[strtolower($currency)] = $coin['current_price'] ?? null;
                        }
                        $map[$cid] = $entry;
                    }
                }
                if (!empty($map)) {
                    return ['data' => $map, 'source' => $markets['source'] . '+markets'];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('CryptoPrice simple fallback failed: ' . $e->getMessage());
        }

        return null;
    }

    // ── CoinGecko ────────────────────────────────────────────────────

    private function fromCoinGeckoMarkets(string $vs, int $perPage, int $page): array
    {
        $base = rtrim(config('services.coingecko_rest.base_url', 'https://api.coingecko.com/api/v3'), '/');
        $proKey = config('services.coingecko_rest.pro_api_key');

        $req = Http::timeout(self::HTTP_TIMEOUT)->acceptJson();
        if ($proKey && str_contains($base, 'pro-api.coingecko.com')) {
            $req = $req->withHeaders(['x-cg-pro-api-key' => $proKey]);
        }

        $resp = $req->get("{$base}/coins/markets", [
            'vs_currency' => $vs,
            'order' => 'market_cap_desc',
            'per_page' => $perPage,
            'page' => $page,
            'sparkline' => 'false',
            'price_change_percentage' => '1h,24h,7d',
        ]);

        if (!$resp->successful()) {
            throw new \RuntimeException("HTTP {$resp->status()}");
        }

        $json = $resp->json();
        if (!$this->isValidMarketsList($json)) {
            // rate-limit: {"status":{"error_code":429,...}}
            $code = is_array($json) ? ($json['status']['error_code'] ?? null) : null;
            throw new \RuntimeException('Invalid/empty CoinGecko response' . ($code ? " (code {$code})" : ''));
        }

        return array_map(fn ($c) => $this->normalizeCoinGecko($c), $json);
    }

    private function fromCoinGeckoSimple(array $ids, array $vs): array
    {
        $base = rtrim(config('services.coingecko_rest.base_url', 'https://api.coingecko.com/api/v3'), '/');
        $proKey = config('services.coingecko_rest.pro_api_key');

        $req = Http::timeout(self::HTTP_TIMEOUT)->acceptJson();
        if ($proKey && str_contains($base, 'pro-api.coingecko.com')) {
            $req = $req->withHeaders(['x-cg-pro-api-key' => $proKey]);
        }

        $resp = $req->get("{$base}/simple/price", [
            'ids' => implode(',', $ids),
            'vs_currencies' => implode(',', $vs),
        ]);

        if (!$resp->successful()) {
            throw new \RuntimeException("HTTP {$resp->status()}");
        }

        $json = $resp->json() ?? [];
        if (!$this->looksLikePriceMap($json)) {
            throw new \RuntimeException('Invalid CoinGecko simple/price response');
        }

        return $json;
    }

    private function normalizeCoinGecko(array $c): array
    {
        return [
            'id' => $c['id'] ?? null,
            'symbol' => $c['symbol'] ?? null,
            'name' => $c['name'] ?? null,
            'image' => $c['image'] ?? $this->fallbackImage($c['symbol'] ?? ''),
            'current_price' => $c['current_price'] ?? null,
            'market_cap' => $c['market_cap'] ?? null,
            'market_cap_rank' => $c['market_cap_rank'] ?? null,
            'total_volume' => $c['total_volume'] ?? null,
            'price_change_percentage_24h' => $c['price_change_percentage_24h']
                ?? $c['price_change_percentage_24h_in_currency']
                ?? null,
            'price_change_percentage_1h_in_currency' => $c['price_change_percentage_1h_in_currency'] ?? null,
            'price_change_percentage_7d_in_currency' => $c['price_change_percentage_7d_in_currency'] ?? null,
        ];
    }

    // ── CryptoCompare ────────────────────────────────────────────────

    private function fromCryptoCompareMarkets(int $perPage, int $page): array
    {
        // page 1 only supported cleanly; extra pages ignored (top list)
        $limit = min(100, max(1, $perPage));
        $resp = Http::timeout(self::HTTP_TIMEOUT)
            ->acceptJson()
            ->get('https://min-api.cryptocompare.com/data/top/mktcapfull', [
                'limit' => $limit,
                'tsym'  => 'USD',
            ]);

        if (!$resp->successful()) {
            throw new \RuntimeException("HTTP {$resp->status()}");
        }

        $json = $resp->json();
        // Rate limit / erreur CC: Message != Success
        if (($json['Response'] ?? null) === 'Error') {
            throw new \RuntimeException($json['Message'] ?? 'CryptoCompare error');
        }

        $rows = $json['Data'] ?? [];
        if (!is_array($rows) || empty($rows)) {
            throw new \RuntimeException('Empty CryptoCompare data');
        }

        $out = [];
        foreach ($rows as $i => $row) {
            $coinInfo = $row['CoinInfo'] ?? [];
            $raw = $row['RAW']['USD'] ?? [];
            $symbol = strtolower((string) ($coinInfo['Name'] ?? $raw['FROMSYMBOL'] ?? ''));
            $name = (string) ($coinInfo['FullName'] ?? $symbol);
            $id = $this->slugId($name, $symbol);
            $image = null;
            if (!empty($coinInfo['ImageUrl'])) {
                $image = 'https://www.cryptocompare.com' . $coinInfo['ImageUrl'];
            }

            $out[] = [
                'id' => $id,
                'symbol' => $symbol,
                'name' => $name,
                'image' => $image ?: $this->fallbackImage($symbol),
                'current_price' => isset($raw['PRICE']) ? (float) $raw['PRICE'] : null,
                'market_cap' => isset($raw['MKTCAP']) ? (float) $raw['MKTCAP'] : null,
                'market_cap_rank' => $i + 1,
                'total_volume' => isset($raw['TOTALVOLUME24HTO']) ? (float) $raw['TOTALVOLUME24HTO'] : null,
                'price_change_percentage_24h' => isset($raw['CHANGEPCT24HOUR'])
                    ? (float) $raw['CHANGEPCT24HOUR']
                    : null,
            ];
        }

        return $out;
    }

    // ── CoinLore ─────────────────────────────────────────────────────

    private function fromCoinLoreMarkets(int $perPage, int $page): array
    {
        $start = max(0, ($page - 1) * $perPage);
        $resp = Http::timeout(self::HTTP_TIMEOUT)
            ->acceptJson()
            ->get('https://api.coinlore.net/api/tickers/', [
                'start' => $start,
                'limit' => $perPage,
            ]);

        if (!$resp->successful()) {
            throw new \RuntimeException("HTTP {$resp->status()}");
        }

        $rows = $resp->json('data') ?? [];
        if (!is_array($rows) || empty($rows)) {
            throw new \RuntimeException('Empty CoinLore data');
        }

        $out = [];
        foreach ($rows as $r) {
            $symbol = strtolower((string) ($r['symbol'] ?? ''));
            $name = (string) ($r['name'] ?? $symbol);
            $out[] = [
                'id' => $this->slugId($name, $symbol),
                'symbol' => $symbol,
                'name' => $name,
                'image' => $this->fallbackImage($symbol),
                'current_price' => isset($r['price_usd']) ? (float) $r['price_usd'] : null,
                'market_cap' => isset($r['market_cap_usd']) ? (float) $r['market_cap_usd'] : null,
                'market_cap_rank' => isset($r['rank']) ? (int) $r['rank'] : null,
                'total_volume' => isset($r['volume24']) ? (float) $r['volume24'] : null,
                'price_change_percentage_24h' => isset($r['percent_change_24h'])
                    ? (float) $r['percent_change_24h']
                    : null,
            ];
        }

        return $out;
    }

    // ── CoinCap ──────────────────────────────────────────────────────

    private function fromCoinCapMarkets(int $perPage, int $page): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $resp = Http::timeout(self::HTTP_TIMEOUT)
            ->acceptJson()
            ->get('https://api.coincap.io/v2/assets', [
                'limit' => $perPage,
                'offset' => $offset,
            ]);

        if (!$resp->successful()) {
            throw new \RuntimeException("HTTP {$resp->status()}");
        }

        $rows = $resp->json('data') ?? [];
        if (!is_array($rows) || empty($rows)) {
            throw new \RuntimeException('Empty CoinCap data');
        }

        $out = [];
        foreach ($rows as $i => $r) {
            $symbol = strtolower((string) ($r['symbol'] ?? ''));
            $out[] = [
                'id' => $r['id'] ?? $symbol,
                'symbol' => $symbol,
                'name' => $r['name'] ?? $symbol,
                'image' => $this->fallbackImage($symbol),
                'current_price' => isset($r['priceUsd']) ? (float) $r['priceUsd'] : null,
                'market_cap' => isset($r['marketCapUsd']) ? (float) $r['marketCapUsd'] : null,
                'market_cap_rank' => isset($r['rank']) ? (int) $r['rank'] : ($offset + $i + 1),
                'total_volume' => isset($r['volumeUsd24Hr']) ? (float) $r['volumeUsd24Hr'] : null,
                'price_change_percentage_24h' => isset($r['changePercent24Hr'])
                    ? (float) $r['changePercent24Hr']
                    : null,
            ];
        }

        return $out;
    }

    private function slugId(string $name, string $symbol): string
    {
        // IDs type coingecko pour les gros connus (liens /crypto/{id})
        static $known = [
            'btc' => 'bitcoin', 'eth' => 'ethereum', 'bnb' => 'binancecoin',
            'sol' => 'solana', 'xrp' => 'ripple', 'ada' => 'cardano',
            'doge' => 'dogecoin', 'trx' => 'tron', 'ton' => 'the-open-network',
            'avax' => 'avalanche-2', 'dot' => 'polkadot', 'link' => 'chainlink',
            'matic' => 'matic-network', 'ltc' => 'litecoin', 'bch' => 'bitcoin-cash',
            'uni' => 'uniswap', 'atom' => 'cosmos', 'xlm' => 'stellar',
            'near' => 'near', 'apt' => 'aptos', 'arb' => 'arbitrum',
            'op' => 'optimism', 'sui' => 'sui', 'shib' => 'shiba-inu',
        ];
        $s = strtolower($symbol);
        if (isset($known[$s])) {
            return $known[$s];
        }
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? $s);
        return trim($slug, '-') ?: $s;
    }

    // ── Binance ──────────────────────────────────────────────────────

    private function fromBinanceMarkets(int $perPage): array
    {
        $symbols = array_keys(self::BINANCE_MAP);
        $take = array_slice($symbols, 0, min($perPage, count($symbols)));

        // Endpoint batch: symbols=["BTCUSDT","ETHUSDT",...]
        $resp = Http::timeout(self::HTTP_TIMEOUT)
            ->acceptJson()
            ->get('https://api.binance.com/api/v3/ticker/24hr', [
                'symbols' => json_encode(array_values($take)),
            ]);

        if (!$resp->successful()) {
            // Fallback: all tickers puis filtrer (plus lourd mais fiable)
            $resp = Http::timeout(self::HTTP_TIMEOUT)
                ->acceptJson()
                ->get('https://api.binance.com/api/v3/ticker/24hr');
            if (!$resp->successful()) {
                throw new \RuntimeException("HTTP {$resp->status()}");
            }
            $all = $resp->json() ?? [];
            $wanted = array_flip($take);
            $rows = array_values(array_filter(
                is_array($all) ? $all : [],
                fn ($t) => isset($t['symbol'], $wanted[$t['symbol']])
            ));
        } else {
            $rows = $resp->json() ?? [];
        }

        if (!is_array($rows) || empty($rows)) {
            throw new \RuntimeException('Empty Binance data');
        }

        $out = [];
        $rank = 1;
        foreach ($rows as $t) {
            $sym = $t['symbol'] ?? null;
            if (!$sym || !isset(self::BINANCE_MAP[$sym])) {
                continue;
            }
            $meta = self::BINANCE_MAP[$sym];
            $price = isset($t['lastPrice']) ? (float) $t['lastPrice'] : null;
            $change = isset($t['priceChangePercent']) ? (float) $t['priceChangePercent'] : null;
            $quoteVol = isset($t['quoteVolume']) ? (float) $t['quoteVolume'] : null;

            $out[] = [
                'id' => $meta['id'],
                'symbol' => $meta['symbol'],
                'name' => $meta['name'],
                'image' => $this->fallbackImage($meta['symbol']),
                'current_price' => $price,
                'market_cap' => null, // Binance n'a pas le market cap
                'market_cap_rank' => $rank++,
                'total_volume' => $quoteVol,
                'price_change_percentage_24h' => $change,
            ];
        }

        if (empty($out)) {
            throw new \RuntimeException('No Binance coins mapped');
        }

        return $out;
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function isValidMarketsList(mixed $data): bool
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        // Doit être une liste indexée de coins, pas un objet d'erreur
        if (array_is_list($data)) {
            $first = $data[0] ?? null;
            return is_array($first) && (
                isset($first['id']) || isset($first['symbol']) || isset($first['current_price'])
            );
        }
        return false;
    }

    private function looksLikePriceMap(mixed $data): bool
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        // {"bitcoin":{"usd":65000}} — pas {"status":{"error_code":429}}
        if (isset($data['status']['error_code'])) {
            return false;
        }
        foreach ($data as $v) {
            if (is_array($v)) {
                return true;
            }
        }
        return false;
    }

    private function fallbackImage(string $symbol): string
    {
        $s = strtolower(trim($symbol));
        if ($s === '') {
            return '/image/coins/default.svg';
        }
        // CDN public icônes crypto (ne dépend pas de CoinGecko)
        return "https://assets.coincap.io/assets/icons/{$s}@2x.png";
    }
}
