<?php

namespace App\Services\Admin;

use App\Repositories\Api\TrophyRepository;
use App\Services\AbstractServices\AbstractTrophyService;
use App\Web3\Pinata;
use App\Web3\TrophyNFT;

class AdminTrophyService extends AbstractTrophyService
{
    public function __construct(TrophyNFT $trophyNFT, Pinata $pinata)
    {
        parent::__construct(
            trophyRepository: new TrophyRepository(),
            trophyNFT: $trophyNFT,
            pinata: $pinata
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
