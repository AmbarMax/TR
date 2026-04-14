<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string $image
 * @property float $price
 * @property float $recieve
 * @property string $description
 */
class Trophy extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public const FILE_PATH = 'app/public/trophies';

    protected $fillable = [
        'name',
        'image',
        'price',
        'receive',
        'description',
        'is_nft',
        'max_supply',
        'minted',
        'key_id',
    ];

    protected $appends = ['key'];

    protected $guarded = [
        'id'
    ];

    public function key()
    {
        return $this->belongsTo(Key::class, 'key_id');
    }

    public function getKeyAttribute()
    {
        return $this->key()->first();
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'postable');
    }

}
