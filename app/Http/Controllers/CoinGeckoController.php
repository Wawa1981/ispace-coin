<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\CoinGeckoRestService;

class CoinGeckoController extends Controller
{
    /** Top N marchés (liste comme ta capture) */
    public function markets(Request $request, CoinGeckoRestService $svc)
    {
        $vs   = $request->query('vs', 'usd');
        $size = (int) $request->query('per_page', 10);
        $page = (int) $request->query('page', 1);

        $key  = "cg:markets:{$vs}:{$size}:{$page}";
        $data = Cache::remember($key, 30, fn () => $svc->getMarkets($vs, $size, $page));

        return response()->json($data);
    }

    /** Prix spot multi-IDs (pour valoriser un portefeuille) */
    public function price(Request $request, CoinGeckoRestService $svc)
    {
        $ids = array_filter(array_map('trim', explode(',', $request->query('ids', 'bitcoin,ethereum'))));
        $vs  = array_filter(array_map('trim', explode(',', $request->query('vs', 'usd'))));

        $key  = 'cg:price:' . md5(json_encode([$ids, $vs]));
        $data = Cache::remember($key, 20, fn () => $svc->getSimplePrice($ids, $vs));

        return response()->json($data);
    }

    /** Bougies OHLC (pour les graphiques chandeliers) */
    public function ohlc(string $id, Request $request, CoinGeckoRestService $svc)
    {
        $vs   = $request->query('vs', 'usd');
        $days = (int) $request->query('days', 1); // 1,7,14,30,90,180,365,max

        $key  = "cg:ohlc:{$id}:{$vs}:{$days}";
        $data = Cache::remember($key, 30, fn () => $svc->getOHLC($id, $vs, $days));

        return response()->json($data);
    }

    /** Bandeau défilant (ce que consomme ton Hello.vue: /api/crypto-prices) */
    public function ticker(CoinGeckoRestService $svc)
    {
        // On prend le top 15 marchés USD et on ne renvoie que les champs nécessaires au ticker
        $data = Cache::remember('cg:ticker:usd:15', 20, fn () => $svc->getMarkets('usd', 15, 1));

        $minimal = array_map(function ($c) {
            return [
                'id'              => $c['id'] ?? null,
                'name'            => $c['name'] ?? null,
                'symbol'          => $c['symbol'] ?? null,
                'current_price'   => $c['current_price'] ?? null,
                'price_change_percentage_24h' => $c['price_change_percentage_24h'] ?? null,
                'image'           => $c['image'] ?? null,
            ];
        }, is_array($data) ? $data : []);

        return response()->json([
            'success' => true,
            'prices'  => $minimal,
        ]);
    }

    /** Market chart (historique prix pour graphiques) */
    public function marketChart(Request $request, CoinGeckoRestService $svc)
    {
        // 💡 L'ancienne ligne : $coinId = $request->query('coinId', 'bitcoin');
        // 🏆 La nouvelle ligne, qui retire la valeur par défaut pour accepter n'importe quel ID.
        $coinId = $request->query('coinId');

        // Ajout d'une vérification pour s'assurer que l'ID est bien présent
        if (!$coinId) {
            return response()->json(['error' => 'Missing coinId parameter'], 400);
        }

        $vs     = $request->query('vs', 'usd');
        $days   = (int) $request->query('days', 30);

        $key    = "cg:market_chart:{$coinId}:{$vs}:{$days}";
        $data = Cache::remember($key, 30, fn () => $svc->getMarketChart($coinId, $vs, $days));

        return response()->json($data);
    }
}
