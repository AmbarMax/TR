<?php

namespace App\Http\Apis\Integrations;

interface ApiIntegrationInterface
{
    public function getBadges();

    public function syncBadges();

    public function setAuthData($data);
}
