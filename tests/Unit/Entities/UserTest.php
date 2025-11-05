<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Movement;
use App\Entity\User;
use App\Tests\TestTraits;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    use TestTraits;
    
    private $user;

    private function createUserWithDefaults(): User
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);
        $user->setFirstName('test');
        $user->setLastName('test');
        $user->setApiToken('test');

        return $user;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWithDefaults();
    }

    public function testEmail(): void
    {
        $this->assertIsString($this->user->getEmail());
    }

    public function testToString(): void
    {
        $this->assertIsString($this->user->__toString());
    }

    public function testUpdateTimestamp(): void
    {
        $this->user->updateTimestamp();
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getUpdatedAt());
    }

    public function testGetId(): void
    {
        $this->setPrivateProperty($this->user, 'id', 99999);
        $this->assertEquals(99999, $this->user->getId());
    }

    public function testGetRoles(): void
    {
        $this->assertIsArray($this->user->getRoles());
    }

    public function testGetPassword(): void
    {
        $this->assertIsString($this->user->getPassword());
    }

    public function testGetFirstName(): void
    {
        $this->assertIsString($this->user->getFirstName());
    }

    public function testGetLastName(): void
    {
        $this->assertIsString($this->user->getLastName());
    }

    public function testGetApiToken(): void
    {
        $this->assertIsString($this->user->getApiToken());
    }

    public function testGetCreatedAt(): void
    {
        $this->setPrivateProperty($this->user, 'createdAt', new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $this->setPrivateProperty($this->user, 'updatedAt', new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getUpdatedAt());
    }
    
    public function testGetUserIdentifier(): void
    {
        $this->assertIsString($this->user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        $this->assertNull($this->user->eraseCredentials());
    }
}
