<?php

namespace App\Service;

use App\Entity\User;

class RoleGetterService
{
  
    private const ROLE_MAP = [
        'ROLE_PROGRAMMATEUR' => 'Programmateur',
        'ROLE_FINANCIER'     => 'Financier',
        'ROLE_TECHNICIEN'    => 'Technicien',
        'ROLE_ADMIN'         => 'Administrateur',
    ];

    /**
     * Retourne les libellés des rôles sous forme de tableau
     *
     * @return string[]
     */
    public function formatUserRoles(User $user): array
    {
        $userRoles = $user->getRoles();
        $formattedRoles = [];

        foreach ($userRoles as $role) {
            if (isset(self::ROLE_MAP[$role])) {
                $formattedRoles[] = self::ROLE_MAP[$role];
            }
        }

        // Rôle par défaut si aucun rôle métier spécifique n'est assigné
        if (empty($formattedRoles)) {
            $formattedRoles[] = 'Utilisateur';
        }

        return $formattedRoles;
    }

    /**
     * Retourne les rôles sous forme de chaîne de caractères (ex: "Programmateur, Financier")
     */
    public function formatUserRolesToString(User $user, string $separator = ', '): string
    {
        return implode($separator, $this->formatUserRoles($user));
    }
}