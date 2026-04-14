<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Api\SteamAchievementSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSteamAchievementsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $connection = 'sync'; // temporary: run inline until Supervisor is configured

    public int $tries = 2;
    public int $timeout = 300; // 5 minutes max — Steam API can be slow with many games

    private string $userId;
    private string $steamId;

    /**
     * @param string $userId User UUID
     * @param string $steamId Steam ID 64
     */
    public function __construct(string $userId, string $steamId)
    {
        $this->userId = $userId;
        $this->steamId = $steamId;
        $this->onQueue('steam-sync');
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::error("SyncSteamAchievementsJob: user {$this->userId} not found");
            return;
        }

        $service = new SteamAchievementSyncService();
        $summary = $service->syncForUser($user, $this->steamId);

        Log::info("SyncSteamAchievementsJob: completed for user {$this->userId}", $summary);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SyncSteamAchievementsJob: FAILED for user {$this->userId}", [
            'steam_id' => $this->steamId,
            'error' => $exception->getMessage(),
        ]);
    }
}
