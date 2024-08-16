<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Movement;
use App\Entity\Recurrence;
use App\Entity\User;
use App\Enums\RecurrenceEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MovementFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 1000; $i++) {

            $user = $users[array_rand($users)];
            $category = $categories[array_rand($categories)];

            $dateAleatoire = $faker->dateTimeBetween('-3 years', 'now', 'Europe/Paris');
            $amount = $faker->randomFloat(2, -2000, 2000);

            // Création d'un mouvement
            $movement = new Movement();
            $movement->setAmount($amount);
            $movement->setDescription($faker->sentence()); 
            $movement->setDate(\DateTimeImmutable::createFromMutable($dateAleatoire));
            $movement->setCategory($category);
            if ($amount < 0) {
                $movement->setType('expense');
            } else {
                $movement->setType('income');
            }
            $movement->setUser($user);

            // Ajout aléatoire d'une récurrence
            if (rand(0, 1)) { // 50% de chance
                $recurrence = new Recurrence();
                $recurrenceName = RecurrenceEnum::cases()[array_rand(RecurrenceEnum::cases())];
                $recurrence->setName($recurrenceName->value);
                $recurrence->setStartDate(\DateTimeImmutable::createFromMutable($dateAleatoire));
                // Définir une date de fin aléatoire après la date de début
                $recurrence->setEndDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween($dateAleatoire, '+3 years', 'Europe/Paris')));
                $movement->setRecurrence($recurrence);

                $manager->persist($recurrence);
            }

            $manager->persist($movement);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
