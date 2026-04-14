<?php

namespace App\Services;

use App\Enums\BadgeType;
use App\Enums\IntegrationType;
use App\Models\Badge;
use App\Models\Integration;
use App\Repositories\BaseRepository;
use App\Repositories\DiscordSFTPRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiscordSFTPService
{
    private BaseRepository $badgeRepository;
    private BaseRepository $integrationRepository;
    private DiscordSFTPRepository $discordSFTPRepository;
    private FileService $fileService;

    public function __construct()
    {
        $this->discordSFTPRepository = new DiscordSFTPRepository();
        $this->fileService = new FileService();
        $this->badgeRepository = new BaseRepository(Badge::class);
        $this->integrationRepository = new BaseRepository(Integration::class);
    }

    public function getBadges()
    {
        try {
            $data = json_decode($this->discordSFTPRepository->getBadges());
        }catch (\Exception $exception){
            Log::error('DiscordSFTPService@getBadges: ' . $exception->getMessage());
            return null;
        }
        return $data;
    }

    public function syncServerBadges()
    {
        try {
            if($data = $this->getBadges()){
                return DB::transaction(function () use ($data) {
                    $integration = $this->integrationRepository->findBy('name', IntegrationType::Discord)->first();
                    if ($integration) {
                        $existingBadges = $this->badgeRepository->findBy('integration_id', $integration->id);
                        foreach ($data->badges as $badge) {
                            $name = $badge->prefix .' '. $badge->name;
                            $image = $this->fileService->getDiscordBotImage($badge->image_url);
                            $image = $image != null ? $image : 'throphybadge.png';

                            if (!$existingBadges->contains('name', $name)) {
                                $createdBadge = $this->badgeRepository->create([
                                    'name' => $name,
                                    'image' => $image,
                                    'type' => BadgeType::DiscordBotBadge,
                                    'integration_id' => $integration->id
                                ]);
                                if ($createdBadge) {
                                    $createdBadge->discordBotBadge()->create([
                                        'discord_id' => $badge->id,
                                        'prefix' => $badge->prefix
                                    ]);
                                }
                            }
                            else{
                                $existBadge = $this->badgeRepository->findBy('name', $name)->first();
                                if ($existBadge->image != $image){
                                    $this->badgeRepository->update($existBadge->id, [
                                        'image' => $image
                                    ]);
                                }
                            }
                        }
                    }
                });
            }else{
                return false;
            }
        }catch (\Exception $exception){
            Log::error('DiscordSFTPService@syncServerBadges: ' . $exception->getMessage());
            return false;
        }
    }

}
