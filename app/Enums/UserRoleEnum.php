<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case Student = 'student';
    case Parent = 'parent';
    case Advisor = 'advisor';
    case Editor = 'editor';
    case Moderator = 'moderator';
    case Analyst = 'analyst';
    case Admin = 'admin';
    case Superadmin = 'superadmin';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Élève / Étudiant',
            self::Parent => 'Parent',
            self::Advisor => 'Conseiller d\'orientation',
            self::Editor => 'Éditeur de contenu',
            self::Moderator => 'Modérateur',
            self::Analyst => 'Analyste',
            self::Admin => 'Administrateur',
            self::Superadmin => 'Super Administrateur',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::Admin, self::Superadmin]);
    }

    public function isSuperAdmin(): bool
    {
        return $this === self::Superadmin;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
