<?php

namespace App\Http\Apis\Integrations\Riot;

use App\Http\Apis\Integrations\ApiIntegrationInterface;

class RiotAdapter implements ApiIntegrationInterface
{
    private RiotApi $riotApi;

    public function __construct()
    {
        $this->riotApi = new RiotApi();
    }

    /**
     * @param string $data  Riot ID in "GameName#TagLine" format
     */
    public function setAuthData($data): void
    {
        $this->riotApi->setAuthData($data);
    }

    public function getBadges(): array
    {
        return $this->riotApi->getBadges();
    }

    public function syncBadges(): void
    {
        // Sync is driven by BadgeService::syncBadges(); nothing to do here.
    }
}
