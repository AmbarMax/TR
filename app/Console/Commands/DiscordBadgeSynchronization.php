<?php

namespace App\Console\Commands;


use App\Services\DiscordSFTPService;
use Illuminate\Console\Command;

class DiscordBadgeSynchronization extends Command
{


    public function __construct(private readonly DiscordSFTPService $discordSFTPService)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:discord-badge-synchronization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync badges from Discord SFTP server to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->discordSFTPService->syncServerBadges();
    }
}
