<?php

namespace App\Services\Api;

use App\Enums\ExchangeStatus;
use App\Models\Badge;
use App\Models\Exchange;
use App\Models\Key;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Api\TrophyRepository;
use App\Repositories\Api\UserRepository;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KeyService
{


    public function index($id)
    {
       //TODO return user keys, not all keys
        return Key::all();
    }

}
