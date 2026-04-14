<?php

namespace App\Models;

use App\Enums\ItemType;
use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Item extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'name',
        'image',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    protected $appends = [
        'typeName',
    ];

    public function getTypeNameAttribute()
    {
        return ItemType::getName($this->type);
    }

    public function chests()
    {
        return $this->belongsToMany(Chest::class, 'chest_item', 'item_id', 'chest_id');
    }
}
