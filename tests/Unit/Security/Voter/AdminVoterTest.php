<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\User;
use App\Security\Voter\AdminVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class AdminVoterTest extends TestCase
{
    private AdminVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new AdminVoter();
    }

    public function testSupportsAllAttributes(): void
    {
        $result = $this->voter->vote(
            $this->createToken(new User()),
            null,
            ['ANY_ATTRIBUTE']
        );

        // Le voter devrait toujours participer au vote
        self::assertNotSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testGrantsAccessToAdmin(): void
    {
        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setRoles(['ROLE_ADMIN']);

        $result = $this->voter->vote(
            $this->createToken($user),
            null,
            ['ANY_ATTRIBUTE']
        );

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testDeniesAccessToNonAdmin(): void
    {
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setRoles(['ROLE_USER']);

        $result = $this->voter->vote(
            $this->createToken($user),
            null,
            ['ANY_ATTRIBUTE']
        );

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testDeniesAccessToAnonymous(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $result = $this->voter->vote(
            $token,
            null,
            ['ANY_ATTRIBUTE']
        );

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    private function createToken(User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        return $token;
    }
}
