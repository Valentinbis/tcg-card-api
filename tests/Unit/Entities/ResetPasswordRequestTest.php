<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Tests\TestTraits;
use PHPUnit\Framework\TestCase;

class ResetPasswordRequestTest extends TestCase
{
    use TestTraits;

    private $resetPasswordRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetPasswordRequest = new ResetPasswordRequest(
            new User(),
            new \DateTime(),
            'selector',
            'hashedToken'
        );
    }

    public function testGetId(): void
    {
        $this->setPrivateProperty($this->resetPasswordRequest, 'id', 99999);
        $this->assertEquals(99999, $this->resetPasswordRequest->getId());

    }

    public function testGetUser(): void
    {
        $this->assertInstanceOf(User::class, $this->resetPasswordRequest->getUser());
    }
}
