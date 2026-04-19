<?php

namespace App\Enums;

enum LevelEnum: string
{
    case Bts = 'bts';
    case Dut = 'dut';
    case Licence = 'licence';
    case Master = 'master';
    case Doctorat = 'doctorat';
    case FormationPro = 'formation_pro';

    public function label(): string
    {
        return match ($this) {
            self::Bts => 'BTS',
            self::Dut => 'DUT',
            self::Licence => 'Licence',
            self::Master => 'Master',
            self::Doctorat => 'Doctorat',
            self::FormationPro => 'Formation Professionnelle',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
