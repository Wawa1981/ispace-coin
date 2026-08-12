<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // BitGo Configuration
    'bitgo' => [
        'base_url' => env('BITGO_BASE_URL'),
        'access_token' => env('BITGO_ACCESS_TOKEN'),
        'wallet_id_eth' => env('BITGO_WALLET_ID_ETH'),
        'wallet_id_btc' => env('BITGO_WALLET_ID_BTC'),
        'webhook_signing_secret' => env('WEBHOOK_SIGNING_SECRET'),
    ],


    // CoinGecko MCP (public keyless pour démarrer) — laissé tel quel
    'coingecko_mcp' => [
        'endpoint' => env('COINGECKO_MCP_URL', 'https://mcp.api.coingecko.com/sse'),
        'protocol_version' => env('MCP_PROTOCOL_VERSION', '2025-06-18'),
    ],

    // CoinGecko REST (Option B) — pour appels backend directs (simple/price, markets, ohlc, trending…)
    'coingecko_rest' => [
        // Public base URL (gratuit)
        'base_url'    => env('COINGECKO_BASE_URL', 'https://api.coingecko.com/api/v3'),
        // Si tu passes sur Pro, change la base_url ci-dessus en https://pro-api.coingecko.com/api/v3
        // et renseigne la clé suivante (envoyée en header x-cg-pro-api-key)
        'pro_api_key' => env('COINGECKO_PRO_API_KEY'),
        // Timeout HTTP des requêtes
        'timeout'     => env('COINGECKO_HTTP_TIMEOUT', 15),
    ],

];
