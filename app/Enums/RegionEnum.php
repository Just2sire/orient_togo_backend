<?php

namespace App\Enums;

enum RegionEnum: string
{
    case Maritime = 'maritime';
    case Plateaux = 'plateaux';
    case Centrale = 'centrale';
    case Kara = 'kara';
    case Savanes = 'savanes';

    public function label(): string
    {
        return match ($this) {
            self::Maritime => 'Région Maritime',
            self::Plateaux => 'Région des Plateaux',
            self::Centrale => 'Région Centrale',
            self::Kara => 'Région de la Kara',
            self::Savanes => 'Région des Savanes',
        };
    }

    // Retourne toutes les valeurs pour les règles de validation
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
