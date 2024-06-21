<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\Movement;
use App\Tests\TestTraits;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    use TestTraits;
    
    private $category;

    private function createCategoryWithDefaults(): Category
    {
        $category = new Category();
        $category->setName('Nom par défaut');
        $category->setParent(new Category());
        $category->addMovement(new Movement());
        $category->addChild(new Category());

        return $category;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = $this->createCategoryWithDefaults();
    }

    public function testName(): void
    {
        $this->assertIsString($this->category->getName());
    }

    public function testParent(): void
    {
        $this->assertInstanceOf(Category::class, $this->category->getParent());
    }

    public function testMovements(): void
    {
        $this->assertInstanceOf(Collection::class, $this->category->getMovements());
    }

    public function testAddMovement(): void
    {
        $this->assertInstanceOf(Collection::class, $this->category->getMovements());
    }

    public function testRemoveMovement(): void
    {
        $this->category->removeMovement($this->category->getMovements()->first());
        $this->assertEmpty($this->category->getMovements());
    }


    public function testRemoveChild(): void
    {
        $this->category->removeChild($this->category->getChildren()->first());
        $this->assertEmpty($this->category->getChildren());
    }

    public function testSetParent(): void
    {
        $parent = new Category();
        $this->category->setParent($parent);
        $this->assertSame($parent, $this->category->getParent());
    }

    public function testGetChildren(): void
    {
        $this->assertInstanceOf(Collection::class, $this->category->getChildren());
    }

    public function testGetParent(): void
    {
        $this->assertInstanceOf(Category::class, $this->category->getParent());
    }

    public function testGetMovements(): void
    {
        $this->assertInstanceOf(Collection::class, $this->category->getMovements());
    }

    public function testGetId(): void
    {
        $this->setPrivateProperty($this->category, 'id', 99999);
        $this->assertEquals(99999, $this->category->getId());
    }

    public function testGetCreatedAt(): void
    {
        $this->setPrivateProperty($this->category, 'createdAt', new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->category->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $this->setPrivateProperty($this->category, 'updatedAt', new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->category->getUpdatedAt());
    }

    public function testUpdateTimestamp(): void
    {
        $this->category->updateTimestamp();    
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->category->getUpdatedAt());
    }
}
