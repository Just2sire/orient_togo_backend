<?php

namespace App\Enums;

enum PlatformEnum: string
{
    case Web = 'web';
    case Android = 'android';
    case iOS = 'ios';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
