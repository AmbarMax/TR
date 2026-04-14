<?php

namespace App\Services\Api;

use App\Jobs\CreateAndSendNotificationJob;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected Dispatcher $eventDispatcher;

    public function __construct(Dispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createAndSendNotification($userId, $type, $title, $description, $creatorId, $entityId)
    {
        try {
            DB::transaction(function () use ($userId, $type, $title, $description, $creatorId, $entityId) {
                dispatch(new CreateAndSendNotificationJob($userId, $type, $title, $description, $creatorId, $entityId));
            });
        } catch (\Exception $e) {
            Log::error('Error creating and sending notification: ' . $e->getMessage());
        }
    }
}