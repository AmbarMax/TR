<?php
namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

class IntegrationType extends Enum
{
    const Github = 'github';
    const Steam = 'steam';
    const Discord = 'discord';
    const Riot = 'riot';
    const Strava = 'strava';

}
