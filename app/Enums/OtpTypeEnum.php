<?php

namespace App\Enums;

enum OtpTypeEnum: string
{
    case Register = 'register';
    case Login = 'login';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
