<?php

namespace App\Jobs;

use App\Events\NotificationCreated;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpcent\Client;

class CreateAndSendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $type;
    protected $title;
    protected $description;
    protected $creatorId;
    protected $entityId;

    public function __construct($userId, $type, $title, $description, $creatorId, $entityId)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->creatorId = $creatorId;
        $this->entityId = $entityId;
    }

    public function handle()
    {
        $notificationData = [
            'user_id' => $this->userId,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'read' => false,
            'entity_id' => $this->entityId,
        ];

        $notification = Notification::create($notificationData);

        $data = [
            'unreadCount' => Notification::getUnreadCount($this->userId),
        ];

         $client = new Client(config('broadcasting.connections.centrifugo.url'));
         $client->setApiKey(config('broadcasting.connections.centrifugo.api_key'));
         $channel = 'notification-user-'.$this->userId;
         $client->publish($channel, $data);

        if ($this->creatorId && in_array($this->type, [Notification::SUBSCRIBE_TYPE, Notification::UNSUBSCRIBE_TYPE])) {

            $channel ='network-'.$this->creatorId;
            $client->publish($channel, ['type' => $this->type, 'userId' => $this->userId]);

            $channel ='network-'.$this->userId;
            $client->publish($channel, ['type' => $this->type]);
        }

        if (Notification::PROOF_TYPE){
            $channel ='proof-'.$this->creatorId;
            $client->publish($channel, ['type' => $this->type]);
        }

        event(new NotificationCreated($notification));
    }
}