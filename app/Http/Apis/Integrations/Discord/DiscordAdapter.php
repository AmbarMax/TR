<?php

namespace App\Http\Apis\Integrations\Discord;

use App\Http\Apis\Integrations\ApiIntegrationInterface;

class DiscordAdapter implements ApiIntegrationInterface
{

    private DiscordApi $discordApi;
    public function __construct()
    {
        $this->discordApi = new DiscordApi();
    }

    public function syncBadges()
    {
        $this->discordApi->syncBadges();
    }

    public function getBadges()
    {
        return $this->discordApi->getBadges();
    }

    public function setAuthData($data)
    {
        $this->discordApi->setUserData($data);
    }
}
