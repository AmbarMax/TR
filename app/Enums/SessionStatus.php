<?php
namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

class SessionStatus extends Enum
{
    const Success = 'success';
    const Info = 'info';
    const Warning = 'warning';
    const Error = 'error';
    const Message = 'message';
}
