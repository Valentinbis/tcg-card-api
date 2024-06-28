<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Movement;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->find(10);
        $category = $manager->getRepository(Category::class)->find(1);

        for ($i=0; $i < 1000; $i++) { 
            $movement = new Movement();
            $movement->setAmount(rand(-1000, 1000));
            $movement->setDate(new \DateTimeImmutable());
            $movement->setCategory($category);
            $movement->setType('income');
            $movement->setUser($user);
            $manager->persist($movement);
            $manager->flush();
        }

    }
}
