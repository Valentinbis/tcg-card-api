<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 50; $i++) {
            // Création d'un user
            $user = new User();
            $user->setEmail($faker->email());
            $user->setPassword($faker->password());
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());

            $manager->persist($user);
        }
        $manager->flush();

        $user = new User();
        $user->setEmail('valentin.bissay@gmail.com');
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                'password'
            )
        );
        $user->setFirstName('Valentin');
        $user->setLastName('Bissay');
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        $userTest = new User();
        $userTest->setEmail('test@test.com');
        $userTest->setPassword(
            $this->userPasswordHasher->hashPassword(
                $userTest,
                'password'
            )
        );
        $userTest->setFirstName('Test');
        $userTest->setLastName('Test');

        $manager->persist($userTest);

        $manager->flush();
    }
}
