<?php

namespace App\Services\Admin;

use App\Repositories\Api\TrophyRepository;
use App\Services\AbstractServices\AbstractTrophyService;

class AdminTrophyService extends AbstractTrophyService
{
    public function __construct()
    {
        parent::__construct(
            trophyRepository: new TrophyRepository(),
        );
    }

    public function getAllTrophies()
    {
        return $this->trophyRepository->getAllTrophies();
    }

    public function getAllTrophiesWithAchievements()
    {
        return $this->trophyRepository->getAllTrophiesWithAchievements();
    }



}
