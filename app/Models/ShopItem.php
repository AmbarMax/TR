<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    use HasFactory, UUID;

    protected $fillable = ['name', 'image', 'description', 'price_uru', 'stock', 'is_active'];

    public function purchases()
    {
        return $this->hasMany(ShopPurchase::class);
    }
}
