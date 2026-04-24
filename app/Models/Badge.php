<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property string $id
 * @property string $name
 * @property string $image
 * @property string $description
 * @property string $integration_id
 * @property int $type
 * @property Model $info
 * @property boolean $display
 */
class Badge extends Model
{
    use HasFactory, UUID, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'image',
        'description',
        'integration_id',
        'type',
        'info'
    ];


    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function trophies()
    {
        return $this->belongsToMany(Trophy::class);
    }

    public function discordRole()
    {
        return $this->hasOne(DiscordRole::class);
    }

    public function discordBotBadge()
    {
        return $this->hasOne(DiscordBotBadge::class);
    }

    public function post()
    {
        return $this->morphOne(Post::class, 'postable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'image', 'deleted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('badge');
    }
}
