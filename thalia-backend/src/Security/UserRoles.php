<?php

namespace App\Security;

final class UserRoles
{
public const USER = 'ROLE_USER';
public const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

public static function getRoles(): array
{
    return [
        'Programmateur' => self::USER,
        'Super Admin' => self::SUPER_ADMIN,

    ];
}

}