<?php

namespace App\Http\Traits;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

trait UUID
{
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if ($model->getKey() === null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    public function shortId(): Attribute
    {
        return new Attribute(
            get: fn () => substr($this->id, -5),
        );
    }
}
