<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CoingeckoMcpService;

class TestCoingeckoMcp extends Command
{
    // On garde ton nom actuel pour éviter toute confusion
    protected $signature = 'app:test-coingecko-mcp';
    protected $description = 'Init MCP + liste des outils (CoinGecko, endpoint public)';

    public function handle(): int
    {
        /** @var CoingeckoMcpService $svc */
        $svc = app(CoingeckoMcpService::class);

        $init = $svc->initialize();
        $this->info('initialize: ' . json_encode($init));

        $tools = $svc->listTools();
        $this->info('tools/list: ' . json_encode($tools));

        return self::SUCCESS;
    }
}
