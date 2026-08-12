<?php

namespace App\Http\Controllers;

use App\Services\YouTubeLiveService;
use Illuminate\Http\Request;

class TvController extends Controller
{
    /**
     * Résout le live YouTube d'une chaîne (videoId réel ou live=false).
     * GET /api/tv/live?channelId=UC...
     */
    public function live(Request $request, YouTubeLiveService $yt)
    {
        $channelId = (string) $request->query('channelId', '');
        if ($channelId === '') {
            return response()->json(['error' => 'Missing channelId'], 400);
        }

        $result = $yt->resolve($channelId);

        return response()->json([
            'success'  => true,
            'channelId'=> $channelId,
            'live'     => (bool) $result['live'],
            'videoId'  => $result['videoId'],
            'embedUrl' => $result['videoId']
                ? 'https://www.youtube.com/embed/' . $result['videoId'] . '?autoplay=0&modestbranding=1&rel=0'
                : null,
        ]);
    }
}
