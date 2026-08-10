// app/Jobs/RefreshWallet.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RefreshWallet implements ShouldQueue
{
    use Queueable;
    public function __construct(public int $userId) {}

    public function handle(): void
    {
        $resp = Http::connectTimeout(3)->timeout(5)->retry(2, 200)
            ->get(env('BITGO_EXPRESS_URL', 'http://127.0.0.1:3080').'/api/v2/ping');
        $data = ['ok' => $resp->ok(), 'at' => now()->toISOString(), 'payload' => $resp->json()];
        Cache::put("wallet:{$this->userId}", $data, now()->addMinute());
    }
}
