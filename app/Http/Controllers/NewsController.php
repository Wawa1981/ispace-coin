<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    /**
     * Actus FR + EN, cachées côté serveur pour éviter le rate-limit CryptoCompare
     * (le navigateur ne tape plus l’API en direct).
     */
    public function index(Request $request)
    {
        $lang = strtoupper($request->query('lang', 'FR'));
        if (! in_array($lang, ['FR', 'EN'], true)) {
            $lang = 'FR';
        }

        $payload = Cache::remember("news:cc:{$lang}", 1800, function () use ($lang) {
            return $this->fetchCryptoCompare($lang);
        });

        // Si cache vide (rate limit au moment du fetch), tenter RSS de secours
        if (empty($payload['articles'])) {
            $payload = Cache::remember("news:rss:{$lang}", 900, function () use ($lang) {
                return $this->fetchRssFallback($lang);
            });
        }

        return response()->json($payload);
    }

    private function fetchCryptoCompare(string $lang): array
    {
        $apiKey = env('VITE_CRYPTOCOMPARE_KEY') ?: env('CRYPTOCOMPARE_API_KEY');
        $url = 'https://min-api.cryptocompare.com/data/v2/news/';

        try {
            $query = ['lang' => $lang];
            if ($apiKey) {
                $query['api_key'] = $apiKey;
            }

            $res = Http::timeout(12)->get($url, $query);

            if (! $res->successful()) {
                Log::warning('CryptoCompare news HTTP '.$res->status());

                return ['source' => 'cryptocompare', 'articles' => [], 'error' => 'http_'.$res->status()];
            }

            $json = $res->json();
            $data = $json['Data'] ?? null;

            // Rate limit renvoie Data: {} (objet vide) + Message
            if (! is_array($data) || $this->isAssocEmpty($data)) {
                Log::warning('CryptoCompare news empty/rate-limit: '.($json['Message'] ?? 'no data'));

                return [
                    'source' => 'cryptocompare',
                    'articles' => [],
                    'error' => $json['Message'] ?? 'empty',
                ];
            }

            // Liste indexée 0..n
            $list = array_is_list($data) ? $data : array_values($data);

            $articles = array_map(function ($a) {
                return [
                    'id' => $a['id'] ?? uniqid('n_', true),
                    'title' => $a['title'] ?? '',
                    'url' => $a['url'] ?? '#',
                    'imageurl' => $a['imageurl'] ?? null,
                    'published_on' => $a['published_on'] ?? null,
                    'source' => $a['source'] ?? null,
                    'source_info' => $a['source_info'] ?? null,
                    'categories' => $a['categories'] ?? null,
                    'body' => isset($a['body']) ? mb_substr(strip_tags($a['body']), 0, 280) : null,
                ];
            }, $list);

            return [
                'source' => 'cryptocompare',
                'articles' => array_values(array_filter($articles, fn ($a) => $a['title'] !== '')),
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('CryptoCompare news: '.$e->getMessage());

            return ['source' => 'cryptocompare', 'articles' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Secours si CryptoCompare est en rate-limit : flux RSS publics.
     */
    private function fetchRssFallback(string $lang): array
    {
        $feeds = $lang === 'FR'
            ? [
                'https://www.journalducoin.com/feed/',
                'https://cryptoast.fr/feed/',
                'https://www.cointribune.com/feed/',
            ]
            : [
                'https://cointelegraph.com/rss',
                'https://www.coindesk.com/arc/outboundfeeds/rss/',
                'https://bitcoinmagazine.com/.rss/full/',
            ];

        $articles = [];

        foreach ($feeds as $feedUrl) {
            try {
                $res = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'iSpaceCoin/1.0'])
                    ->get($feedUrl);

                if (! $res->successful()) {
                    continue;
                }

                $xml = @simplexml_load_string($res->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
                if (! $xml) {
                    continue;
                }

                $items = $xml->channel->item ?? $xml->item ?? [];
                $i = 0;
                foreach ($items as $item) {
                    if ($i++ >= 20) {
                        break;
                    }
                    $title = trim((string) ($item->title ?? ''));
                    $link = trim((string) ($item->link ?? ''));
                    if ($title === '' || $link === '') {
                        continue;
                    }

                    $image = $this->extractRssImage($item);

                    $pub = strtotime((string) ($item->pubDate ?? '')) ?: null;

                    $articles[] = [
                        'id' => md5($link),
                        'title' => $title,
                        'url' => $link,
                        'imageurl' => $image,
                        'published_on' => $pub,
                        'source' => parse_url($feedUrl, PHP_URL_HOST),
                        'source_info' => ['name' => parse_url($feedUrl, PHP_URL_HOST)],
                        'categories' => null,
                        'body' => null,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('RSS news fail '.$feedUrl.': '.$e->getMessage());
            }
        }

        return [
            'source' => 'rss',
            'articles' => $articles,
            'error' => count($articles) ? null : 'rss_empty',
        ];
    }

    private function isAssocEmpty(array $data): bool
    {
        return $data === [] || (array_keys($data) !== range(0, count($data) - 1) && count($data) === 0);
    }

    /**
     * Extrait une URL d’image depuis un item RSS (enclosure, media:*, content HTML).
     * Décode les URLs type https%3A%2F%2F… (ExactDN / Journal du Coin).
     */
    private function extractRssImage(\SimpleXMLElement $item): ?string
    {
        $candidates = [];

        if (isset($item->enclosure['url'])) {
            $candidates[] = (string) $item->enclosure['url'];
        }

        $media = $item->children('media', true);
        if ($media) {
            if (isset($media->content)) {
                foreach ($media->content as $content) {
                    $attrs = $content->attributes();
                    if (isset($attrs['url'])) {
                        $candidates[] = (string) $attrs['url'];
                    }
                }
            }
            if (isset($media->thumbnail)) {
                foreach ($media->thumbnail as $thumb) {
                    $attrs = $thumb->attributes();
                    if (isset($attrs['url'])) {
                        $candidates[] = (string) $attrs['url'];
                    }
                }
            }
        }

        // content:encoded (souvent l’image à la une)
        $contentNs = $item->children('http://purl.org/rss/1.0/modules/content/', true);
        $htmlBlobs = [];
        if ($contentNs && isset($contentNs->encoded)) {
            $htmlBlobs[] = (string) $contentNs->encoded;
        }
        if (isset($item->description)) {
            $htmlBlobs[] = (string) $item->description;
        }

        foreach ($htmlBlobs as $html) {
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
                $candidates[] = $m[1];
            }
            if (preg_match('/srcset=["\']([^"\']+)["\']/i', $html, $m)) {
                // premier URL du srcset
                $part = trim(explode(',', $m[1])[0]);
                $candidates[] = trim(explode(' ', $part)[0]);
            }
        }

        foreach ($candidates as $raw) {
            $url = $this->normalizeImageUrl($raw);
            if ($url) {
                return $url;
            }
        }

        return null;
    }

    private function normalizeImageUrl(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        $url = trim($raw);

        // Double-encodage fréquent : https%3A%2F%2F…
        if (str_contains($url, '%3A%2F%2F') || str_contains($url, '%3a%2f%2f')) {
            $url = rawurldecode($url);
        }

        // Encore une couche si besoin
        if (str_contains($url, '%3A%2F%2F') || str_contains($url, '%3a%2f%2f')) {
            $url = rawurldecode($url);
        }

        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $url = trim($url);

        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return null;
        }

        // ignore 1x1 trackers / data uris inutiles
        if (str_starts_with($url, 'data:')) {
            return null;
        }

        return $url;
    }
}
