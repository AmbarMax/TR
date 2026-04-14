<?php

namespace App\Http\Apis\Integrations\Github;

use App\Enums\BadgeType;
use Laravel\Socialite\Facades\Socialite;
use Weidner\Goutte\GoutteFacade;

class GithubApi
{
    private $username;
    private $urlMain = 'https://github.com';
    private $urlAchievements = '/users/%s/achievements';


    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getBadges()
    {
        return $this->getListOfAchievements();
    }


    private function getListOfAchievements()
    {
        $crawler = GoutteFacade::request('GET', $this->getAchievementUrl());
        $data = [];

        $crawler->filter('details.js-achievement-card-details')->each(function ($node) use (&$data) {
            $title = $node->filter('h3')->text();

            $filename = basename(parse_url(
                $node->filter('img')->attr('src')
            )['path']);

            $data[] = [
                'name' => $title,
                'image' => $filename,
                'type' => BadgeType::Common
            ];
        });
        return $data;

    }

    private function getAchievementUrl()
    {
        return $this->urlMain . sprintf($this->urlAchievements, $this->username);
    }


}
