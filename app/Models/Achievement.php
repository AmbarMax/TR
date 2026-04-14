<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property string $id
 * @property string $user_id
 * @property string $status
 * @property string $image
 * @property string $name
 * @property string $display
 * @property string $description
 * @property boolean $is_share
 * @property string $amount
 * @property string $created_at
 * @property string $updated_at
 */
class Achievement extends Model
{
    use HasFactory, UUID;

    const VALIDATED = 1;
    const NOT_VALIDATED = 2;
    const PENDING_STATUS = 3;

    const SOCIAL_PROOF = 1;
    const RUNE_PROOF = 2;

    const RUNE_PROOF_COST = 5;

    const AMBAR_COST = 200;
    const AMBAR_CREATE_ACHIEVEMENT = 100;

    /** @inheritdoc */
    protected $table = 'achievements';

    protected $fillable = [
        'status',
        'image',
        'name',
        'description',
        'is_share',
        'amount',
        'display'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function posts(): MorphMany
    {
        return $this->morphMany(Post::class, 'postable');
    }
}
