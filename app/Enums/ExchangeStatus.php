<?php

namespace App\Enums;

class ExchangeStatus
{
    const PENDING = 0;
    const PAID = 1;
    const CANCELED = 2;

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::PENDING => 'Pending',
            static::PAID => 'Paid',
            static::CANCELED => 'Canceled',
        ];
    }

    public static function getName($key)
    {
        return static::map()[$key] ?? null;
    }

    public static function getKey($name)
    {
        $map = array_flip(static::map());
        return $map[$name] ?? null;
    }

}
