<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;

class DiscordSFTPRepository
{
    public function getBadges()
    {
        return Storage::disk('sftp')->get('badge_db.json');
    }

}
