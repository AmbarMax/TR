<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $donatable_id
 * @property string $donatable_type
 * @property float $amount
 * @property string $currency_id
 * @property string $payer_id
 * @property string $receiver_id
 * @property string $user_id
 */
class Donation extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'donatable_id',
        'donatable_type',
        'amount',
        'currency_id',
        'payer_id',
        'receiver_id',
        'user_id',
    ];

    public function donatable()
    {
        return $this->morphTo();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class);
    }
}
