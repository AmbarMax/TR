<?php

namespace App\Services\Api;

use App\Enums\IntegrationType;
use App\Http\Apis\Integrations\ApiIntegrationInterface;
use App\Http\Apis\Integrations\Discord\DiscordAdapter;
use App\Http\Apis\Integrations\Github\GithubAdapter;
use App\Http\Apis\Integrations\Riot\RiotAdapter;
use App\Http\Apis\Integrations\Steam\SteamAdapter;
use App\Models\AuthIntegration;
use App\Models\Badge;
use App\Models\Integration;
use App\Models\User;
use App\Repositories\Api\AuthRepository;
use App\Repositories\Api\UserRepository;
use App\Repositories\BaseRepository;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

class BadgeService
{
    private ApiIntegrationInterface $apiIntegration;
    private BaseRepository $badgeRepository;
    private BaseRepository $integrationRepository;
    private string $integrationType;

    public function __construct(private readonly UserRepository $userRepository,
                                private readonly AuthRepository $authRepository,
                                private readonly FileService $fileService)
    {
        $this->badgeRepository = new BaseRepository(Badge::class);
        $this->integrationRepository = new BaseRepository(Integration::class);
    }

    public function getAll()
    {
        return $this->badgeRepository->findAll();
    }

    public function setApiIntegration(ApiIntegrationInterface $apiIntegration)
    {
        $this->apiIntegration = $apiIntegration;
        switch ($apiIntegration){
            case $apiIntegration instanceof GithubAdapter:
                $this->integrationType = IntegrationType::Github;
                break;
            case $apiIntegration instanceof SteamAdapter:
                $this->integrationType = IntegrationType::Steam;
                break;
            case $apiIntegration instanceof DiscordAdapter:
                $this->integrationType = IntegrationType::Discord;
                break;
            case $apiIntegration instanceof RiotAdapter:
                $this->integrationType = IntegrationType::Riot;
                break;
            default:
                $this->integrationType = IntegrationType::Github;
        }
    }

    public function syncBadges($auth)
    {
        $this->apiIntegration->setAuthData($auth);
        return DB::transaction(function () use ($auth){
            try {
                $data = $this->synchronize($this->integrationType,
                    $this->apiIntegration->getBadges());
                if ($data) {
                    Session::flash('achievements', json_encode($data));
                    return true;
                }
                return false;
            }catch (\Exception $exception){
                if ($this->integrationType == IntegrationType::Discord){
                    $auth = $auth['id'];
                }
                Log::error('BadgeService@getBadges(username: '.$auth.', integration: '.$this->integrationType.' ):'
                    . $exception->getMessage());
                return false;
            }
        });
    }

    public function synchronize(string $integrationType, array $badges)
    {
        return DB::transaction(function () use ($integrationType, $badges) {
            $integration = $this->integrationRepository->findBy('name', $integrationType)->first();
            $existingBadges = $this->badgeRepository->findBy('integration_id', $integration->id);
            $data = [];
            if ($integration) {
                foreach ($badges as $badge) {
                    if ($existingBadges->contains('name', $badge['name'])) {
                        $nameToFind = $badge['name'];
                        $data[] = $existingBadges->first(function ($item) use ($nameToFind) {
                            return $item['name'] === $nameToFind;
                        })->id;
                    } else {
                        $badge['integration_id'] = $integration->id;
                        if ($image = $this->fileService->saveIntegrationImage($integrationType, $badge['image'], $badge['type'])){
                            $badge['image'] = $image;
                            $data[] = $this->badgeRepository->create($badge)->id;
                        }
                    }
                }
                return $data;
            }
            return null;
        });
    }

    public function auth($integrationUser, $integrationType) {
        return true;
     /*   $integration = $this->authRepository->findIntegration($integrationUser->getId(), $integrationType);
        if ($integration->count() == 0) {
            try {
                DB::beginTransaction();
                $user = $this->userRepository->create(
                    [
                        'name' => $integrationUser->nickname,
                        'username' => $integrationUser->nickname
                    ]);
                $this->fileService->setAvatar($user->id, file_get_contents($integrationUser->avatar));
                $this->authRepository->create(
                    [
                        'name' => $integrationType,
                        'integration_id' => $integrationUser->id,
                        'user_id' => $user->id
                    ]);
                DB::commit();
                return true;
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
        else {
            $user = $integration[0]->user;
            return false;
        }*/
       /* $token = JWTAuth::fromUser($user);
        Session::flash('token', $token);
        Session::flash('user', json_encode($user));*/
    }

}
