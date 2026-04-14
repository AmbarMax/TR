<?php

namespace App\Models;

use App\Enums\ItemType;
use App\Http\Traits\UUID;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Chest extends Model
{
    use HasFactory, UUID;

    public const FILE_PATH = 'app/public/chests';

    protected $fillable = [
        'name',
        'image_closed',
        'image_open',
        'description',
        'quantity',
        'is_hidden',
        'is_nft',
        'key_id',
        'expiration_date'
    ];

    protected $appends = ['key', 'expiration_date_show_format'];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'chest_item', 'chest_id', 'item_id');
    }

    public function key()
    {
        return $this->belongsTo(Key::class, 'key_id');
    }

    public function getKeyAttribute()
    {
        return $this->key()->first();
    }

    public function getExpirationDateShowFormatAttribute()
    {
        return Carbon::parse($this->expiration_date)->format('M d, Y');
    }

}
