<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {   
        for ($i=0; $i < 100; $i++) { 
            // Création d'une catégorie
            $category = new Category();
            $category->setName('Category '.$i);
            $manager->persist($category);
        }

        $manager->flush();

    }
}
