<?php

namespace App\Enum;

enum UserRole: string
{
    case USER = 'ROLE_USER';
    case PROGRAMMATEUR = 'ROLE_PROGRAMMATEUR';
    case FINANCIER = 'ROLE_FINANCIER';
    case TECHNICIEN = 'ROLE_TECHNICIEN';

    /**
     * Libellé lisible pour l'interface utilisateur
     */
    public function label(): string
    {
        return match($this) {
            self::USER => 'Utilisateur',
            self::PROGRAMMATEUR => 'Programmation',
            self::FINANCIER => 'Administation, finances',
            self::TECHNICIEN => 'Technique',
        };
    }

    /**
     * Vérifie si une valeur scalaire (ex: depuis un formulaire) correspond à un rôle valide
     */
    public static function isValid(string $role): bool
    {
        return self::tryFrom($role) !== null;
    }
}