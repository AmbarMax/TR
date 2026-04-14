<?php

namespace App\Http\Apis\Integrations\Steam;

use App\Enums\BadgeType;
use Illuminate\Support\Facades\Http;
use Weidner\Goutte\GoutteFacade;

class SteamApi
{
    private $id;
    private $key;
    private $urlMain = 'https://steamcommunity.com/';
    private $urlAchievement = '/profiles/%s/badges/%s';
    private $urlGameCards = '/profiles/%s/gamecards/%s/';

    private $urlApiBadge = 'https://api.steampowered.com/IPlayerService/GetBadges/v1?key=%s&steamid=%s';

    public function setUserId($id)
    {
        $this->id = $id;
        $this->key = env('STEAM_SECRET');
    }

    public function getBadges()
    {
        return $this->getListOfAchievements();
    }

    private function getListOfAchievements()
    {
        $response = Http::get($this->getApiUserBadgesUrl());
        $badges = $response->json()['response']['badges'];
        $data = [];
        foreach ($badges as $badge){
            if (isset($badge['appid'])){
                $data[]= $this->scrapGameCards($badge['appid']);
            } else {
                $data[]= $this->scrapBadge($badge['badgeid']);
            }
        }
        return $data;
    }

    private function scrapBadge($id){
        $crawler = GoutteFacade::request('GET', $this->getAchievementUrl($id));
        $data = [];

        $crawler->filter('.badge_row_inner')->each(function ($node) use (&$data) {
            $title = $node->filter('.badge_title')->text();

            $filename= $node->filter('.badge_info_image img')->attr('src');

            $description = $node->filter('.badge_description')->text();

            $data = [
                'name' => $title,
                'image' => $filename,
                'description' => $description,
                'type' => BadgeType::Common
            ];
        });
        return $data;
    }

    private function scrapGameCards($id){
        $crawler = GoutteFacade::request('GET', $this->getGameCardsUrl($id));
        $data = [];

        $crawler->filter('.badge_row_inner')->each(function ($node) use (&$data) {
            $title = $node->filter('.badge_title')->text();

            $filename= $node->filter('.badge_info_image img')->attr('src');

            $description = "";

            $data = [
                'name' => $title,
                'image' => $filename,
                'description' => $description,
                'type' => BadgeType::Common
            ];
        });
        return $data;
    }

    private function getAchievementUrl($badgeId)
    {
        return $this->urlMain . sprintf($this->urlAchievement, $this->id, $badgeId);
    }

    private function getGameCardsUrl($badgeId)
    {
        return $this->urlMain . sprintf($this->urlGameCards, $this->id, $badgeId);
    }

    private function getApiUserBadgesUrl()
    {
        return sprintf($this->urlApiBadge, $this->key, $this->id);
    }

}
