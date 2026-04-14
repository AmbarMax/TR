<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarkNotificationsAsRead implements ShouldQueue
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        Notification::where('user_id', $this->user->id)
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->update(['read' => true]);
    }
}