<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {   
        $faker = Factory::create('fr_FR');
        
        for ($i=0; $i < 50; $i++) { 
            // Création d'un user
            $user = new User();
            $user->setEmail($faker->email());
            $user->setPassword($faker->password());
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());

            $manager->persist($user);
        }

        $manager->flush();

    }
}
