<?php

namespace App\Http\Apis\Integrations\Github;

use App\Http\Apis\Integrations\ApiIntegrationInterface;

class GithubAdapter implements ApiIntegrationInterface
{
    private GithubApi $githubApi;
    public function __construct()
    {
        $this->githubApi = new GithubApi();
    }

    public function syncBadges()
    {
        return null;
    }

    public function getBadges()
    {
        return $this->githubApi->getBadges();
    }

    public function setAuthData($data)
    {
        $this->githubApi->setUsername($data);
    }
}
