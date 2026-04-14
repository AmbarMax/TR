<?php

namespace App\Enums;

class ItemType
{
    const SKIN = 0;
    const DISCOUNT = 1;

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::SKIN => 'Skin',
            static::DISCOUNT => 'Discount',
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
