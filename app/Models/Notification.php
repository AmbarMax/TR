<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Notification
 * @package App\Models
 *
 * @property int     $id
 * @property string  $user_id
 * @property int     $type
 * @property string  $description
 * @property boolean $read
 * @property string  $entity_id
 * @property string  $created_at
 * @property string  $updated_at
 *
 * @property-read BelongsTo|User $user
 *
 * @mixin Builder
 */
class Notification extends Model
{
    /** @inheritdoc */
    protected $table = 'notifications';

    const SUBSCRIBE_TYPE = 1;
    const UNSUBSCRIBE_TYPE = 2;
    const INSUFFICIENT_FUNDS_TYPE = 3;
    const DONATE_TYPE = 4;
    const PROOF_TYPE = 5;
    const EXCHANGE_TYPE = 6;
    const INSUFFICIENT_FUNDS_TITLE = 'Insufficient funds';

    protected $fillable = [
        'user_id', 'type', 'title', 'description', 'read', 'entity_id'
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('F j, Y');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public static function getUnreadCount($userId): int
    {
        return self::where('user_id', $userId)->where('read', false)->count();
    }
}
