<?php

namespace App\Http\Apis\Integrations\Steam;

use App\Http\Apis\Integrations\ApiIntegrationInterface;

class SteamAdapter implements ApiIntegrationInterface
{
    private SteamApi $steamApi;
    public function __construct()
    {
        $this->steamApi = new SteamApi();
    }

    public function syncBadges()
    {
        return null;
    }

    public function getBadges()
    {
        return $this->steamApi->getBadges();
    }

    public function setAuthData($data)
    {
        $this->steamApi->setUserId($data);
    }
}
