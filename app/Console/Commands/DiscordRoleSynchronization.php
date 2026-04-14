<?php

namespace App\Console\Commands;

use App\Enums\BadgeType;
use App\Enums\IntegrationType;
use App\Models\Badge;
use App\Models\DiscordRole;
use App\Models\Integration;
use App\Repositories\BaseRepository;
use App\Services\DiscordSFTPService;
use App\Services\FileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordRoleSynchronization extends Command
{
    private BaseRepository $integrationRepository;
    private BaseRepository $badgeRepository;
    private FileService $fileService;
    private $urlDiscordRoleImage = "https://cdn.discordapp.com/role-icons/%s/%s.png";
    private $urlMain = 'https://discord.com/api/v10/guilds/%s/%s';
    private $ambarGuild= '947900641366401054';
    private $botHeader = 'Bot %s';



    public function __construct()
    {
        parent::__construct();
        $this->fileService = new FileService();
        $this->discordSFTPService = new DiscordSFTPService();
        $this->integrationType = IntegrationType::Discord;
        $this->integrationRepository = new BaseRepository(Integration::class);
        $this->badgeRepository = new BaseRepository(Badge::class);
        $this->discordRoleRepository = new BaseRepository(DiscordRole::class);
        $this->botToken = env('DISCORD_BOT_TOKEN');
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:discord-role-synchronization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->syncronizeServerRoles($this->ambarGuild);
    }

    private function syncronizeServerRoles($guildId){
        $resp = Http::withHeaders(['Authorization' => $this->getBotHeader()])
            ->get($this->getRolesUrl($guildId));
        if ($resp->successful()){
            $integration = $this->integrationRepository->findBy('name', $this->integrationType)->first();
            foreach ($resp->json() as $role){
//                dd($resp->json());
                $systemRole = $this->discordRoleRepository->findBy('discord_id', $role['id']);
                if($systemRole->isEmpty()){
                    try {
                        DB::beginTransaction();
                        if (isset($role['icon'])){
                            $image = $this->fileService->saveDiscordRoleImage(
                                $this->getRoleImageUrl($role['id'], $role['icon']), $role['icon']);
                        }else{
                            $image = $this->fileService->saveIntegrationImage(
                                $this->integrationType,
                                'throphybadge.png',
                                BadgeType::DiscordRole);
                        }
                        $badge = $this->badgeRepository->create([
                            'name' => $role['name'],
                            'description' => $role['description'] ?? null,
                            'type' => BadgeType::DiscordRole,
                            'image' => $image,
                            'integration_id' => $integration->id,
                        ]);

                        $badge->discordRole()->create([
                            'discord_id' => $role['id'],
                            'permissions' => $role['permissions'],
                            'position' => $role['position'],
                            'color' => $role['color'],
                            'hoist' => $role['hoist'],
                            'managed' => $role['managed'],
                            'mentionable' => $role['mentionable'],
                            'icon' => $role['icon'],
                            'unicode_emoji' => $role['unicode_emoji'],
                            'tags' => isset($role['tags']) ? $role['tags'] : null,
                            'flags' => $role['flags'],
                        ]);
                        DB::commit();
                    }catch (\Exception $exception) {
                        DB::rollBack();
                        Log::error('DiscordApi@syncronizeServerRoles(guildId: '.$guildId.'):'
                            . $exception->getMessage());
                    }
                }else{
                    $systemRole = $systemRole->first();
                    if (isset($role['icon'])) {
                        if ($systemRole->icon != $role['icon']) {
                            try {
                                DB::beginTransaction();
                                $image = $this->fileService->saveDiscordRoleImage(
                                    $this->getRoleImageUrl($role['id'], $role['icon']), $role['icon']);
                                $systemRole->update([
                                    'icon' => $role['icon'],
                                ]);
                                $this->badgeRepository->update($systemRole->badge_id, [
                                    'image' => $image
                                ]);
                                DB::commit();
                            }catch (\Exception $exception) {
                                DB::rollBack();
                                Log::error('DiscordApi@syncronizeServerRoles(guildId: '.$guildId.'):'
                                    . $exception->getMessage());
                            }
                        }
                    }
                }
            }
        }
    }


    private function getRoleImageUrl($roleId, $icon){
        return sprintf($this->urlDiscordRoleImage, $roleId, $icon);
    }

    private function getRolesUrl($guildId){
        return sprintf($this->urlMain, $guildId, 'roles');
    }

    private function getBotHeader(){
        return sprintf($this->botHeader, $this->botToken);
    }

}
