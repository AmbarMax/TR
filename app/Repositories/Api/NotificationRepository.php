<?php

namespace App\Repositories\Api;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getNotificationPaginated(User $user, int $page, ?int $count = null): LengthAwarePaginator
    {
        return $user->notifications()->select('id', 'title', 'type', 'created_at', 'entity_id', 'read', 'entity_id')
            ->orderBy('created_at', 'desc')
            ->orderBy('read', 'asc')
            ->paginate($count, ['*'], 'page', $page);
    }
}