<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $postable_id
 * @property string $postable_type
 * @property string $user_id
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 */
class Post extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'postable_id',
        'postable_type',
        'user_id',
    ];

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('F j, Y');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function postable()
    {
        return $this->morphTo();
    }

    public function donations()
    {
        return $this->morphMany(Donation::class, 'donatable');
    }

    public function uniquePayerCount(): int
    {
        return $this->donations()->distinct('payer_id')->count('payer_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getDonationsSum()
    {
        return $this->donations()->sum('amount');
    }

    public function getLastComments()
    {
        return $this->comments()->orderBy('created_at', 'desc')->take(3)->get();
    }
}
