<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Résout le vrai videoId d'un live YouTube pour une chaîne.
 * L'embed "live_stream?channel=…" est très fragile (souvent "Video unavailable"
 * si la chaîne n'est pas en direct ou n'autorise pas l'embed).
 */
final class YouTubeLiveService
{
    private const CACHE_TTL = 90; // 1.5 min
    private const HTTP_TIMEOUT = 12;

    /**
     * @return array{live: bool, videoId: ?string, title: ?string}
     */
    public function resolve(string $channelId): array
    {
        $channelId = trim($channelId);
        if ($channelId === '' || ! str_starts_with($channelId, 'UC')) {
            return ['live' => false, 'videoId' => null, 'title' => null];
        }

        $key = "yt:live:v2:{$channelId}";
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $result = $this->resolveViaInnertube($channelId);

        // Si innertube ne trouve rien, scrape HTML /live
        if (empty($result['live']) || empty($result['videoId'])) {
            $html = $this->resolveViaHtmlScrape($channelId);
            if (! empty($html['live']) && ! empty($html['videoId'])) {
                $result = $html;
            }
        }

        // Vérifie live en cours OU premiere/upcoming (ex. FT festival) — pas un vieux replay
        if (! empty($result['videoId']) && ! empty($result['live'])) {
            $status = $this->checkVideoLiveStatus($result['videoId']);
            if ($status === 'offline') {
                $result = ['live' => false, 'videoId' => null, 'title' => null];
            }
            // 'live' | 'upcoming' | 'unknown' → on garde le videoId pour l’embed
        }

        Cache::put($key, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * @return array{live: bool, videoId: ?string, title: ?string}
     */
    private function resolveViaInnertube(string $channelId): array
    {
        try {
            $resp = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Content-Type' => 'application/json',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->post('https://www.youtube.com/youtubei/v1/navigation/resolve_url?prettyPrint=false', [
                    'context' => [
                        'client' => [
                            'clientName' => 'WEB',
                            'clientVersion' => '2.20250320.01.00',
                            'hl' => 'en',
                            'gl' => 'US',
                        ],
                    ],
                    'url' => "https://www.youtube.com/channel/{$channelId}/live",
                ]);

            if (! $resp->successful()) {
                throw new \RuntimeException('HTTP '.$resp->status());
            }

            $json = $resp->json() ?? [];

            // Cas 1 : watchEndpoint direct (live en cours)
            $videoId = $this->extractVideoId($json);
            if ($videoId) {
                return [
                    'live' => true,
                    'videoId' => $videoId,
                    'title' => null,
                ];
            }

            // Cas 2 : browseEndpoint (onglet live) → browse pour trouver un live
            $browseId = data_get($json, 'endpoint.browseEndpoint.browseId');
            $params = data_get($json, 'endpoint.browseEndpoint.params');
            if (is_string($browseId) && $browseId !== '') {
                $fromBrowse = $this->resolveViaBrowse($browseId, is_string($params) ? $params : null);
                if ($fromBrowse['live']) {
                    return $fromBrowse;
                }
            }

            return ['live' => false, 'videoId' => null, 'title' => null];
        } catch (\Throwable $e) {
            Log::warning("YouTubeLive innertube failed [{$channelId}]: ".$e->getMessage());

            return ['live' => false, 'videoId' => null, 'title' => null];
        }
    }

    /**
     * @return array{live: bool, videoId: ?string, title: ?string}
     */
    private function resolveViaBrowse(string $browseId, ?string $params): array
    {
        try {
            $payload = [
                'context' => [
                    'client' => [
                        'clientName' => 'WEB',
                        'clientVersion' => '2.20250320.01.00',
                        'hl' => 'en',
                        'gl' => 'US',
                    ],
                ],
                'browseId' => $browseId,
            ];
            if ($params) {
                $payload['params'] = $params;
            }

            $resp = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Content-Type' => 'application/json',
                ])
                ->post('https://www.youtube.com/youtubei/v1/browse?prettyPrint=false', $payload);

            if (! $resp->successful()) {
                return ['live' => false, 'videoId' => null, 'title' => null];
            }

            $raw = $resp->body();

            // Cherche un rendu avec badge LIVE NOW
            if (preg_match(
                '/BADGE_STYLE_TYPE_LIVE_NOW.{0,400}?"videoId"\s*:\s*"([a-zA-Z0-9_-]{11})"|"videoId"\s*:\s*"([a-zA-Z0-9_-]{11})".{0,400}?BADGE_STYLE_TYPE_LIVE_NOW/s',
                $raw,
                $m
            )) {
                $vid = $m[1] !== '' ? $m[1] : ($m[2] ?? null);
                if ($vid) {
                    return ['live' => true, 'videoId' => $vid, 'title' => null];
                }
            }

            if (preg_match('/"isLiveNow"\s*:\s*true.{0,300}?"videoId"\s*:\s*"([a-zA-Z0-9_-]{11})"/s', $raw, $m)) {
                return ['live' => true, 'videoId' => $m[1], 'title' => null];
            }

            return ['live' => false, 'videoId' => null, 'title' => null];
        } catch (\Throwable $e) {
            Log::warning('YouTubeLive browse failed: '.$e->getMessage());

            return ['live' => false, 'videoId' => null, 'title' => null];
        }
    }

    /**
     * Scrape HTML de /channel/UC…/live (fallback).
     *
     * @return array{live: bool, videoId: ?string, title: ?string}
     */
    private function resolveViaHtmlScrape(string $channelId): array
    {
        try {
            $resp = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Cookie' => 'CONSENT=YES+cb.20210328-17-p0.en+FX+000',
                ])
                ->get("https://www.youtube.com/channel/{$channelId}/live");

            if (! $resp->successful()) {
                return ['live' => false, 'videoId' => null, 'title' => null];
            }

            $html = $resp->body();

            // Live en cours uniquement
            if (preg_match('/"isLiveNow"\s*:\s*true/', $html)
                && preg_match('/"videoId"\s*:\s*"([a-zA-Z0-9_-]{11})"/', $html, $m)) {
                return ['live' => true, 'videoId' => $m[1], 'title' => null];
            }

            // Canonical watch URL + isLiveNow elsewhere
            if (preg_match('/rel="canonical"\s+href="https:\/\/www\.youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})"/', $html, $m)) {
                if (str_contains($html, '"isLiveNow":true') || str_contains($html, 'isLiveNow":true')) {
                    return ['live' => true, 'videoId' => $m[1], 'title' => null];
                }
            }

            return ['live' => false, 'videoId' => null, 'title' => null];
        } catch (\Throwable $e) {
            Log::warning("YouTubeLive HTML scrape failed [{$channelId}]: ".$e->getMessage());

            return ['live' => false, 'videoId' => null, 'title' => null];
        }
    }

    /**
     * @return 'live'|'upcoming'|'offline'|'unknown'
     */
    private function checkVideoLiveStatus(string $videoId): string
    {
        try {
            $resp = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Cookie' => 'CONSENT=YES+cb.20210328-17-p0.en+FX+000; SOCS=CAISNQgDEitib3FfaWRlbnRpdHlmcm9udGVuZHVpc2VydmVyXzIwMjMwODI5LjA3X3AxGgJlbiACGgYIgLC_pwY',
                ])
                ->withOptions(['allow_redirects' => true])
                ->get("https://www.youtube.com/watch?v={$videoId}");

            if (! $resp->successful()) {
                return 'unknown';
            }

            $html = $resp->body();

            // Direct en cours
            if (str_contains($html, '"isLiveNow":true') || str_contains($html, 'isLiveNow":true')) {
                return 'live';
            }

            // Premiere / live programmé (FT etc.) — on embed quand même
            if (str_contains($html, '"isUpcoming":true') || str_contains($html, 'isUpcoming":true')) {
                if (str_contains($html, '"isLiveContent":true') || str_contains($html, 'isLiveContent":true')) {
                    return 'upcoming';
                }
            }

            // Replay live terminé
            if (str_contains($html, '"isLiveNow":false') || str_contains($html, 'isLiveNow":false')) {
                return 'offline';
            }

            return 'unknown';
        } catch (\Throwable $e) {
            return 'unknown';
        }
    }

    private function extractVideoId(array $json): ?string
    {
        // endpoint.watchEndpoint.videoId
        $vid = data_get($json, 'endpoint.watchEndpoint.videoId')
            ?? data_get($json, 'endpoint.urlEndpoint.url');

        if (is_string($vid) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $vid)) {
            return $vid;
        }

        if (is_string($vid) && preg_match('/[?&]v=([a-zA-Z0-9_-]{11})/', $vid, $m)) {
            return $m[1];
        }

        // fallback recursive search only for watchEndpoint context
        $watch = data_get($json, 'endpoint.watchEndpoint');
        if (is_array($watch) && isset($watch['videoId']) && is_string($watch['videoId'])) {
            if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $watch['videoId'])) {
                return $watch['videoId'];
            }
        }

        return null;
    }
}
