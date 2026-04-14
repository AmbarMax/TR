<?php

namespace App\Repositories;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use phpcent\Client;
class CentrifugoRepository
{
    private $client;
    private $channel;

    public function __construct()
    {
        $this->client = new Client(config('broadcasting.connections.centrifugo.url'));
        $this->client->setApiKey(config('broadcasting.connections.centrifugo.api_key'));
    }

    public function setChannel($channel)
    {
        Log::info($this->getChannelName($channel));
        $this->channel = $this->getChannelName($channel);
    }

    public function publish($data)
    {
        $this->client->publish($this->channel, $data);
    }

    public function generateToken($user)
    {
        return $this->client
            ->setSecret(config('broadcasting.connections.centrifugo.secret'))
            ->generateConnectionToken($user->id);
    }

    private function getChannelName($channel)
    {
        return config('broadcasting.connections.centrifugo.channel_prefix') . $channel;
    }
}
