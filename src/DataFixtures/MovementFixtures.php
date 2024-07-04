<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Movement;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MovementFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->find(10);
        
        
        for ($i=0; $i < 1000; $i++) { 
            $anneeAleatoire = rand(date('Y') - 10, date('Y'));
            $moisAleatoire = rand(1, 12);
            $jourAleatoire = rand(1, 28);
            $dateAleatoire = new \DateTimeImmutable("$anneeAleatoire-$moisAleatoire-$jourAleatoire");
            $amount = rand(-1000, 1000);
            $category = $manager->getRepository(Category::class)->findRandomCategory();

            // Création d'un mouvement
            $movement = new Movement();
            $movement->setAmount($amount);
            $movement->setDate($dateAleatoire);
            $movement->setCategory($category);
            if ($amount < 0) {
                $movement->setType('expense');
            } else {
                $movement->setType('income');
            }            
            $movement->setUser($user);
            $manager->persist($movement);
        }

        $manager->flush();

    }
}
