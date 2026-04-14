<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Jobs\MarkNotificationsAsRead;
use App\Models\User;
use App\Repositories\Api\NotificationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $page = $request->input('page', 1);
        $count = $request->input('count', 10);

        $notifications = $this->notificationRepository->getNotificationPaginated($user, $page, $count);

        dispatch(new MarkNotificationsAsRead($user));

        return response()->json([$notifications, 'unreadCount' => $user->unreadNotificationsCount()]);
    }
}