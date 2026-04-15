<?php

namespace App\Http\Apis\Integrations\Strava;

use App\Http\Apis\Integrations\ApiIntegrationInterface;

class StravaAdapter implements ApiIntegrationInterface
{
    private StravaApi $stravaApi;

    public function __construct()
    {
        $this->stravaApi = new StravaApi();
    }

    public function setAuthData($data): void
    {
        $this->stravaApi->setAuthData($data);
    }

    public function getBadges(): array
    {
        return $this->stravaApi->getBadges();
    }

    public function syncBadges(): void { }
}
