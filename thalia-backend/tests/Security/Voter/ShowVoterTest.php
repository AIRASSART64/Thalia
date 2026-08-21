<?php

namespace App\Tests\Security\Voter;

use App\Entity\Organization;
use App\Entity\Show;
use App\Entity\User;
use App\Enum\UserRole;
use App\Security\Voter\ShowVoter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ShowVoterTest extends TestCase
{
    private Security&MockObject $security;
    private ShowVoter $voter;

    protected function setUp(): void
    {
        // On mock la dépendance externe du service
        $this->security = $this->createMock(Security::class);
        $this->voter = new ShowVoter($this->security);
    }

    /**
     * Teste qu'un utilisateur non connecté est systématiquement refusé.
     */
    public function testVoteFailsIfUserIsNotLoggedIn(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $result = $this->voter->vote($token, new Show(), [ShowVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * Teste la création d'un spectacle selon le rôle (utilisation directe de UserRole::PROGRAMMATEUR).
     */
    public function testCanCreateShowOnlyWithProgrammationRole(): void
    {
        $user = new User();
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        // Simulation : Security->isGranted(...) renvoie true pour le rôle de programmation
        $this->security
            ->method('isGranted')
            ->with(UserRole::PROGRAMMATEUR->value)
            ->willReturn(true);

        $result = $this->voter->vote($token, null, [ShowVoter::CREATE]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * Teste l'édition d'un spectacle selon l'organisation de l'utilisateur.
     */
    public function testEditFailsIfUserBelongsToDifferentOrganization(): void
    {
        $user = new User();
        $orgA = new Organization();
        $orgB = new Organization();
        
        // On associe deux organisations différentes
        $user->setOrganization($orgA);
        
        $show = new Show();
        $show->setOrganization($orgB);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        // L'utilisateur a le bon rôle, mais pas la bonne organisation
        $this->security->method('isGranted')->willReturn(true);

        $result = $this->voter->vote($token, $show, [ShowVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * Teste l'accès autorisé si l'organisation correspond.
     */
    public function testEditGrantedIfUserBelongsToSameOrganization(): void
    {
        $user = new User();
        $org = new Organization();
        $user->setOrganization($org);

        $show = new Show();
        $show->setOrganization($org);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->security->method('isGranted')->willReturn(true);

        $result = $this->voter->vote($token, $show, [ShowVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }
}