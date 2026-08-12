<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\CoinGeckoRestService;
use App\Services\CryptoPriceService;
use App\Services\MarketTickerService;

class CoinGeckoController extends Controller
{
    /** Top N marchés — multi-source + last-good (jamais d'écran vide) */
    public function markets(Request $request, CryptoPriceService $prices)
    {
        $vs   = $request->query('vs', 'usd');
        $size = max(1, min(250, (int) $request->query('per_page', 10)));
        $page = max(1, (int) $request->query('page', 1));

        $result = $prices->getMarkets($vs, $size, $page);

        // Compat front existant: corps = liste de coins
        // Headers pour debug / indicateur stale côté client
        return response()
            ->json($result['data'])
            ->header('X-Price-Source', $result['source'] ?? 'none')
            ->header('X-Price-Stale', ($result['stale'] ?? false) ? '1' : '0')
            ->header('X-Price-Fetched-At', (string) ($result['fetched_at'] ?? ''));
    }

    /** Prix spot multi-IDs (pour valoriser un portefeuille) */
    public function price(Request $request, CryptoPriceService $prices)
    {
        $ids = array_filter(array_map('trim', explode(',', $request->query('ids', 'bitcoin,ethereum'))));
        $vs  = array_filter(array_map('trim', explode(',', $request->query('vs', 'usd'))));

        $result = $prices->getSimplePrice($ids, $vs);

        return response()
            ->json($result['data'])
            ->header('X-Price-Source', $result['source'] ?? 'none')
            ->header('X-Price-Stale', ($result['stale'] ?? false) ? '1' : '0');
    }

    /** Bougies OHLC (pour les graphiques chandeliers) — CoinGecko only + last-good */
    public function ohlc(string $id, Request $request, CoinGeckoRestService $svc)
    {
        $vs   = $request->query('vs', 'usd');
        $days = (int) $request->query('days', 1);

        $freshKey = "cg:ohlc:{$id}:{$vs}:{$days}";
        $lastKey  = "cg:ohlc:last_good:{$id}:{$vs}:{$days}";

        $data = Cache::get($freshKey);
        if ($data === null) {
            try {
                $data = $svc->getOHLC($id, $vs, $days);
                if (is_array($data) && !empty($data) && array_is_list($data)) {
                    Cache::put($freshKey, $data, 60);
                    Cache::put($lastKey, $data, 86400);
                } else {
                    $data = Cache::get($lastKey, []);
                }
            } catch (\Throwable $e) {
                $data = Cache::get($lastKey, []);
            }
        }

        return response()->json($data);
    }

    /**
     * Bandeau défilant — crypto + devises fiat + actions.
     * Multi-source + last-good par segment (pas de coupure UI).
     */
    public function ticker(MarketTickerService $ticker)
    {
        $board = $ticker->getBoard();
        $items = $board['items'] ?? [];

        return response()->json([
            'success'    => !empty($items),
            'prices'     => $items, // compat front (liste d'items)
            'items'      => $items,
            'sources'    => $board['sources'] ?? [],
            'stale'      => (bool) ($board['stale'] ?? false),
            'fetched_at' => $board['fetched_at'] ?? null,
        ]);
    }

    /** Market chart (historique prix pour graphiques) — CoinGecko + last-good */
    public function marketChart(Request $request, CoinGeckoRestService $svc)
    {
        $coinId = $request->query('coinId');

        if (!$coinId) {
            return response()->json(['error' => 'Missing coinId parameter'], 400);
        }

        $vs   = $request->query('vs', 'usd');
        $days = (int) $request->query('days', 30);

        $freshKey = "cg:market_chart:{$coinId}:{$vs}:{$days}";
        $lastKey  = "cg:market_chart:last_good:{$coinId}:{$vs}:{$days}";

        $data = Cache::get($freshKey);
        if ($data === null) {
            try {
                $data = $svc->getMarketChart($coinId, $vs, $days);
                if (is_array($data) && !empty($data['prices'] ?? $data)) {
                    Cache::put($freshKey, $data, 60);
                    Cache::put($lastKey, $data, 86400);
                } else {
                    $data = Cache::get($lastKey, ['prices' => [], 'market_caps' => [], 'total_volumes' => []]);
                }
            } catch (\Throwable $e) {
                $data = Cache::get($lastKey, ['prices' => [], 'market_caps' => [], 'total_volumes' => []]);
            }
        }

        return response()->json($data);
    }
}
