<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Movement;
use App\Entity\Recurrence;
use App\Tests\TestTraits;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class RecurrenceTest extends TestCase
{
    use TestTraits;
    
    private $recurrence;

    private function createUserWithDefaults(): Recurrence
    {
        $recurrence = new Recurrence();
        $recurrence->setName('Nom par défaut');
        $recurrence->setStartDate(new \DateTimeImmutable());
        $recurrence->setEndDate(new \DateTimeImmutable("+1 month"));
        $recurrence->setLastGeneratedDate(new \DateTimeImmutable());
        $recurrence->addMovement(new Movement());

        return $recurrence;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->recurrence = $this->createUserWithDefaults();
    }

    public function testName(): void
    {
        $this->assertIsString($this->recurrence->getName());
    }

    public function testStartDate(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->recurrence->getStartDate());
    }

    public function testEndDate(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->recurrence->getEndDate());
    }

    public function testToString(): void
    {
        $this->assertIsString($this->recurrence->__toString());
    }

    public function testGetMovements(): void
    {
        $this->assertInstanceOf(Collection::class, $this->recurrence->getMovements());
    }

    public function testAddMovement(): void
    {
        $this->assertInstanceOf(Collection::class, $this->recurrence->getMovements());
    }

    public function testRemoveMovement(): void
    {
        $this->recurrence->removeMovement($this->recurrence->getMovements()->first());
        $this->assertEmpty($this->recurrence->getMovements());
    }

    public function testGetId(): void
    {
        $this->setPrivateProperty($this->recurrence, 'id', 999999);
        $this->assertEquals(999999, $this->recurrence->getId());
    }

    public function testUpdateTimestamp(): void
    {
        $this->recurrence->updateTimestamp();    
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->recurrence->getUpdatedAt());
    }

    public function testGetNullFormatedStartDate(): void
    {
        $this->recurrence->setStartDate(null);
        $this->assertNull($this->recurrence->getFormatedStartDate());
    }

    public function testGetNullFormatedEndDate(): void
    {
        $this->recurrence->setEndDate(null);
        $this->assertNull($this->recurrence->getFormatedEndDate());
    }

    public function testGetFormatedStartDate(): void
    {
        $this->assertIsString($this->recurrence->getFormatedStartDate());
    }

    public function testGetFormatedEndDate(): void
    {
        $this->assertIsString($this->recurrence->getFormatedEndDate());
    }

    public function testGetCreatedAt(): void
    {
        $this->setPrivateProperty($this->recurrence, 'createdAt', new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->recurrence->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $this->recurrence->setUpdatedAt(new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->recurrence->getUpdatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $this->setPrivateProperty($this->recurrence, 'updatedAt', new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->recurrence->getUpdatedAt());
    }

    public function testGetLastGeneratedDate(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->recurrence->getLastGeneratedDate());
    }
}