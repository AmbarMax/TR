<?php

namespace App\Repositories\Api;

use App\Models\Trophy;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Log;

class TrophyRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(Trophy::class);
    }

    public function getAllTrophies()
    {
        return $this->model->orderBy('created_at', 'desc')->get();
    }

    public function getAllTrophiesWithAchievements()
    {
        return $this->model
            ->with('badges.integration')
            ->with('key')
            ->where(function ($query) {
                $query->where('is_nft' , false)
                    ->orWhere(function ($query) {
                        $query->whereColumn('minted', '<', 'max_supply');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
