<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Movement;
use App\Entity\Recurrence;
use App\Entity\User;
use App\Enums\MovementEnum;
use App\Tests\TestTraits;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class MovementTest extends TestCase
{
    use TestTraits;
    
    private $movement;

    private function createUserWithDefaults(): Movement
    {
        $movement = new Movement();
        $movement->setAmount(100);
        $movement->setDate(new \DateTimeImmutable());
        $movement->setDescription('test');
        $movement->setType(MovementEnum::Expense->value);
        $movement->setUser(new User());
        $movement->setRecurrence(new Recurrence());
        $movement->setParent(new Movement());

        return $movement;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->movement = $this->createUserWithDefaults();
    }

    public function testAmount(): void
    {
        $this->assertIsFloat($this->movement->getAmount());
    }

    public function testDate(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->movement->getDate());
    }

    public function testDescription(): void
    {
        $this->assertIsString($this->movement->getDescription());
    }

    public function testType(): void
    {
        $this->assertEquals(MovementEnum::Expense->value, $this->movement->getType());
    }

    public function testGetDate(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->movement->getDate());
    }

    public function testGetFormatedDate(): void
    {
        $this->assertIsString($this->movement->getFormatedDate());
    }

    public function testGetCreatedAt(): void
    {
        $this->setPrivateProperty($this->movement, 'createdAt', new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->movement->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $this->setPrivateProperty($this->movement, 'updatedAt', new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->movement->getUpdatedAt());
    }

    public function testGetRecurrence(): void
    {
        $this->assertInstanceOf(Recurrence::class, $this->movement->getRecurrence());
    }

    public function testGetParent(): void
    {
        $this->assertInstanceOf(Movement::class, $this->movement->getParent());
    }

    public function testGetChildren(): void
    {
        $this->assertInstanceOf(Collection::class, $this->movement->getChildren());
    }

    public function testGetId(): void
    {
        $this->setPrivateProperty($this->movement, 'id', 999999);
        $this->assertEquals(999999, $this->movement->getId());
    }

    public function testUpdateTimestamp(): void
    {
        $this->movement->updateTimestamp();    
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->movement->getUpdatedAt());
    }
}