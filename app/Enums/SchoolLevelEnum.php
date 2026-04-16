<?php

namespace App\Enums;

enum SchoolLevelEnum: string
{
    case Primary = 'primary';
    case MiddleSchool = 'middle_school';
    case HighSchool = 'high_school';
    case HigherEd = 'higher_education';
    case Parent = 'parent';
    case Advisor = 'advisor';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Primaire',
            self::MiddleSchool => 'Collège',
            self::HighSchool => 'Lycée',
            self::HigherEd => 'Enseignement supérieur',
            self::Parent => 'Parent',
            self::Advisor => 'Conseiller d\'orientation',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
