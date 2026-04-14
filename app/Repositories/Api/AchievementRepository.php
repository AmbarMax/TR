<?php

namespace App\Repositories\Api;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AchievementRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getAchievementsPaginated(User $user, int $page, ?int $count = null, $status): LengthAwarePaginator
    {
        $query = $user->achievements()->select('id', 'user_id', 'image', 'name', 'status', 'description', 'is_share', 'display', 'amount', 'created_at')
            ->orderBy('created_at', 'desc');

        if ($status !== null) {
            $query->where('status', $status);
        }
        return $query->paginate($count, ['*'], 'page', $page);
    }
}
