<?php

namespace App\Http\Apis\Integrations\Discord;

use App\Enums\BadgeType;
use App\Enums\DiscordBadges;
use App\Enums\IntegrationType;
use App\Models\Badge;
use App\Models\DiscordBotBadge;
use App\Models\DiscordRole;
use App\Models\Integration;
use App\Repositories\BaseRepository;
use App\Services\DiscordSFTPService;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordApi
{

    private DiscordSFTPService $discordSFTPService;

    private $id;
    private $flags;

    private $botToken;
    private $botHeader = 'Bot %s';
    private $ambarGuild= '947900641366401054';
    private $urlMain = 'https://discord.com/api/v10/guilds/%s/%s';


    public function __construct()
    {
        $this->discordSFTPService = new DiscordSFTPService();
        $this->integrationType = IntegrationType::Discord;
        $this->integrationRepository = new BaseRepository(Integration::class);
        $this->badgeRepository = new BaseRepository(Badge::class);
        $this->discordRoleRepository = new BaseRepository(DiscordRole::class);

    }

    public function setUserData($data)
    {
        $this->id = $data['id'];
        $this->flags = $data['flags'];
        $this->botToken = env('DISCORD_BOT_TOKEN');
    }

    public function getBadges()
    {
        return array_merge(
            $this->getListOfAchievements(),
            $this->getUserRoles(),
            $this->syncDiscordBotBadges()
        );
    }

    private function syncDiscordBotBadges()
    {
        $data = $this->discordSFTPService->getBadges();
        $badges = [];

        if ($data){
            try {
                DB::beginTransaction();
                foreach ($data->users as $userId => $userBadges){
//                    Mock id 981664874318934076
                    if ($userId == $this->id){
                        foreach ($userBadges as $userBadge => $status){
                            $badge = Badge::query()
                                ->whereHas('discordBotBadge', function ($query) use ($userBadge){
                                    $query->where('discord_id', $userBadge);
                                })->first();
                            if ($badge) {
                                $badges [] = [
                                    'name' => $badge->name,
                                    'image' => $badge->image,
                                    'type' => BadgeType::DiscordBotBadge,
                                ];
                            }
                        }
                    }
                }
                DB::commit();
            }catch (\Exception $exception){
                DB::rollBack();
                Log::error('DiscordApi@syncDiscordBotBadges: ' . $exception->getMessage());
                }
            }
        return $badges;
    }

    private function getUserRoles($check = false){
        $data = [];
        $resp = Http::withHeaders(['Authorization' => $this->getBotHeader()])
            ->get($this->getUserRolesUrl($this->ambarGuild, $this->id));
        if ($resp->successful()){
            foreach ($resp->json()['roles'] as $role){
                $existingRole = $this->discordRoleRepository->findBy('discord_id', $role)->first();
                if ($existingRole || $check){
                    $badge = $existingRole->badge;
                    $data[] = [
                        'name' => $badge->name,
                        'description' => $badge->description,
                        'image' => $badge->image,
                        'type' => BadgeType::DiscordRole,
                    ];
                } else {
                    return $this->getUserRoles(true);
                }
            }
        }
        return $data;
    }


    private function getBotHeader(){
        return sprintf($this->botHeader, $this->botToken);
    }

    private function getUserRolesUrl($guildId, $userId){
        return sprintf($this->urlMain, $guildId, 'members/'.$userId);
    }

    private function getListOfAchievements(){
        $data = [];
        $badges = $this->getUserBadges($this->flags);
        foreach ($badges as $badge) {
            $data[] = [
                'name' => DiscordBadges::getStringForBadge($badge),
                'image' => DiscordBadges::getImageForBadge($badge),
                'type' => BadgeType::DiscordBadge,
            ];
        }
        return $data;
    }

    private function getUserBadges($flags) {
        $badgeMappings = [
            DiscordBadges::STAFF => 0,
            DiscordBadges::PARTNER => 1,
            DiscordBadges::HYPESQUAD => 2,
            DiscordBadges::BUG_HUNTER_LEVEL_1 => 3,
            DiscordBadges::HYPESQUAD_ONLINE_HOUSE_1 => 6,
            DiscordBadges::HYPESQUAD_ONLINE_HOUSE_2 => 7,
            DiscordBadges::HYPESQUAD_ONLINE_HOUSE_3 => 8,
            DiscordBadges::PREMIUM_EARLY_SUPPORTER => 9,
            DiscordBadges::TEAM_PSEUDO_USER => 10,
            DiscordBadges::BUG_HUNTER_LEVEL_2 => 14,
            DiscordBadges::VERIFIED_BOT => 16,
            DiscordBadges::VERIFIED_DEVELOPER => 17,
            DiscordBadges::CERTIFIED_MODERATOR => 18,
            DiscordBadges::BOT_HTTP_INTERACTIONS => 19,
            DiscordBadges::ACTIVE_DEVELOPER => 22,
        ];

        $userBadges = [];

        foreach ($badgeMappings as $badge => $position) {
            if ($flags & (1 << $position)) {
                $userBadges[] = $badge;
            }
        }
        return $userBadges;
    }
}
