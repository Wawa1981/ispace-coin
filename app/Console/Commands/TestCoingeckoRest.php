<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CoinGeckoRestService;

class TestCoingeckoRest extends Command
{
    protected $signature = 'app:test-coingecko-rest';
    protected $description = 'Smoke test REST CoinGecko: simple price, trending, markets, ohlc';

    public function handle(): int
    {
        $svc = app(CoinGeckoRestService::class);

        $this->info('simple/price BTC,ETH vs USD:');
        $this->line(json_encode($svc->getSimplePrice(['bitcoin','ethereum'], ['usd']), JSON_PRETTY_PRINT));

        $this->info('search/trending:');
        $this->line(json_encode($svc->getTrending(), JSON_PRETTY_PRINT));

        $this->info('coins/markets top10 USD:');
        $this->line(json_encode($svc->getMarkets('usd', 10, 1), JSON_PRETTY_PRINT));

        $this->info('coins/{id}/ohlc BTC 1d:');
        $this->line(json_encode($svc->getOHLC('bitcoin', 'usd', 1), JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
