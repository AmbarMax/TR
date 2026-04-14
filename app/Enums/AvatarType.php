<?php
namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

class AvatarType extends Enum
{
    const Original = 'original';
    const Medium = 'medium';
    const Small = 'small';

    public static function Original()
    {
        return new self(self::Original);
    }

    public static function Medium()
    {
        return new self(self::Medium);
    }

    public static function Small()
    {
        return new self(self::Small);
    }
}
