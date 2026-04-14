<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $name
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    const MODERATOR = 'Master user';

    /**
     * Attributes that must be converted to instances Carbon.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Indicator whether indexes should be created automatically.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Field defining the model key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Event before saving the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
