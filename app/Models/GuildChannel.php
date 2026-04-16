<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuildChannel extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'guild_id',
        'channel_id',
        'name',
        'type',
        'category',
    ];

    public function guildConnection()
    {
        return $this->belongsTo(GuildConnection::class, 'guild_id', 'guild_id');
    }
}
