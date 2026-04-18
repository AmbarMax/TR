<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BattlePassLevel extends Model
{
    use HasFactory, UUID;

    protected $fillable = ['number', 'name', 'cost_uru', 'rewards'];

    protected $casts = [
        'rewards' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'battle_pass_user')
            ->withTimestamps();
    }
}
