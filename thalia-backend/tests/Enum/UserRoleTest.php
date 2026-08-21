<?php

namespace App\Tests\Enum;

use App\Enum\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    /**
     * Vérifie la valeur stockée de chaque rôle.
     */
    public function testEnumValues(): void
    {
        $this->assertSame('ROLE_USER', UserRole::USER->value);
        $this->assertSame('ROLE_PROGRAMMATEUR', UserRole::PROGRAMMATEUR->value);
        $this->assertSame('ROLE_FINANCIER', UserRole::FINANCIER->value);
        $this->assertSame('ROLE_TECHNICIEN', UserRole::TECHNICIEN->value);
    }

    /**
     * Vérifie le libellé renvoyé par la méthode label().
     */
    public function testLabel(): void
    {
        $this->assertSame('Utilisateur', UserRole::USER->label());
        $this->assertSame('Programmation', UserRole::PROGRAMMATEUR->label());
        $this->assertSame('Administartion, finances', UserRole::FINANCIER->label());
        $this->assertSame('Technique', UserRole::TECHNICIEN->label());
    }

    /**
     * Utilisation d'un Data Provider pour tester proprement isValid().
     *
     * @dataProvider provideRoleValidationData
     */
    public function testIsValid(string $role, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, UserRole::isValid($role));
    }

    public static function provideRoleValidationData(): iterable
    {
        yield 'rôle utilisateur valide' => ['ROLE_USER', true];
        yield 'rôle financier valide' => ['ROLE_FINANCIER', true];
        yield 'rôle inconnu invalide' => ['ROLE_INVALID', false];
        yield 'chaîne vide' => ['', false];
        yield 'casse incorrecte' => ['role_user', false];
    }
}