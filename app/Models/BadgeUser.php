<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'badge_user';

    protected $fillable = [
        'user_id',
        'badge_id',
        'display',
        'is_share',
    ];

    protected $casts = [
        'display'  => 'boolean',
        'is_share' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
