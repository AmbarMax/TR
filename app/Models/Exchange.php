<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Exchange extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'input_amount',
        'input_currency',
        'output_amount',
        'output_currency',
        'wallet_number',
        'status',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
