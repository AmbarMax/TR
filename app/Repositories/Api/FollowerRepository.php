<?php

namespace App\Repositories\Api;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FollowerRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getFollowersPaginated(User $user, int $page, ?int $count = null): LengthAwarePaginator
    {
        return $user->followers()->paginate($count, ['*'], 'page', $page);
    }

    public function getFollowingPaginated(User $user, int $page, ?int $count = null): LengthAwarePaginator
    {
        return $user->following()->paginate($count, ['*'], 'page', $page);
    }
}