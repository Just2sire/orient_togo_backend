<?php

namespace App\Enums;

enum EstablishmentTypeEnum: string
{
    case Lycee = 'lycee';
    case Universite = 'universite';
    case EcolePrivee = 'ecole_privee';
    case CentreFormation = 'centre_formation';

    public function label(): string
    {
        return match ($this) {
            self::Lycee => 'Lycée',
            self::Universite => 'Université',
            self::EcolePrivee => 'École privée',
            self::CentreFormation => 'Centre de formation',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
