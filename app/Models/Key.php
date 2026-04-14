<?php

namespace App\Models;

use App\Enums\ItemType;
use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Key extends Model
{
    use HasFactory, UUID;

    public const FILE_PATH = 'app/public/keys';

    protected $fillable = [
        'name',
        'quantity',
        'image',
        'hash_code'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

}
