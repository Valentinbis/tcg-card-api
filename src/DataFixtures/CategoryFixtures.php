<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enums\MovementEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Transport' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Accessoires pour venicule',
                    'Carburant',
                    'Entretien de véhicule',
                    'Location de voiture et moto',
                    'Peage et Sationnement',
                    'Taxis et VTC',
                    'Trains, avions et ferrys',
                    'Transports en commun',
                    'vélo',
                    'Transports - autre'
                ]
            ],
            'Shopping et services' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Animaux domestiques',
                    'Cadeaux et dons',
                    'Coiffeurs et soins du corps',
                    'High-Tech/Electroménager',
                    'Hygiène',
                    'Papeterie et fournitures',
                    'Poste et colis',
                    'Pressing',
                    'Tabac-Presse',
                    'Vêtements et chaussures',
                    'Aides et services à domicile',
                    'Alcool',
                    'Animaux domestiques',
                    'Cadeaux et dons',
                    'Coiffeurs et soins du corps',
                    'High-Tech/Electroménager',
                    'Hygiène',
                    'Papeterie et fournitures',
                    'Poste et colis',
                    'Pressing',
                    'Tabac-Presse',
                    'Shopping et services - autre'
                ]
            ],
            'Santé' => [
                'types' => MovementEnum::Expense,
                'subCategories' => ['Consultation médicale', 'Mutuelle', 'Opticien', 'Pharmacie', 'Santé - autre']
            ],
            'Revenus et rentrées d\'argent' => [
                'types' => MovementEnum::Income,
                'subCategories' => [
                    'Aides et allocations',
                    'Dividendes et placements',
                    'Dons et cadeaux reçus',
                    'Note de frais',
                    'Pension alimentaire',
                    'Pension de retraite',
                    'Remboursements de soins',
                    'Revenus locatifs',
                    'Salaires',
                    'Revenus - autre'
                ]
            ],
            'Loisirs et vacances' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Camping',
                    'Jeux et jouets',
                    'Parc d\'attraction',
                    'Sorties',
                    'Bar',
                    'Expo, musée, cinéma',
                    'Hôtel',
                    'Livres, Magazines',
                    'Sport, Gym et Equipement',
                    'Vidéo, Musique et jeux',
                    'Loisirs et vacances - autre'
                ]
            ],
            'Logement' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Ameublement et appareil',
                    'Assurance habitation',
                    'Crédit immobilier',
                    'Énergie (eau, gaz, électricité, ...)',
                    'Internet et téléphonie',
                    'Loyers et traites',
                    'Travaux - Entretien',
                    'Logement - autre'
                ]
            ],
            'Juridique et administratif' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Avocats',
                    'Frais d\'huissiers',
                    'Pension alimentaire versée',
                    'Juridique et administratif - autre'
                ]
            ],
            'Impôts et taxes' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Impôts sur le revenu',
                    'ISF',
                    'Taxes d\'habitation et foncière',
                    'Impôts et taxes - autre'
                ]
            ],
            'Épargne' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Assurance vie',
                    'Épargne logement',
                    'Épargne retraite',
                    'Livrets',
                    'Placement financier',
                    'Épargne - autre'
                ]
            ],
            'Alimentation' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Hyper/supermarché',
                    'Petit commerçant',
                    'Restaurant',
                    'Restauration rapide',
                    'Alimentation - autre'
                ]
            ],
            'Banque et assurances' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Assurance auto/moto',
                    'Assurance habitation',
                    'Assurance vie',
                    'Crédit',
                    'Crédit consommation',
                    'Frais bancaires',
                    'Frais d\'incidents',
                    'Titres',
                    'Banque et assurance - autre'
                ]
            ],
            'Éducation et famille' => [
                'types' => MovementEnum::Expense,
                'subCategories' => [
                    'Assurance scolaire',
                    'Fournitures scolaires',
                    'Garde d\'enfant',
                    'Scolarité et études',
                    'Soutien scolaire',
                    'Education et Famille - autre'
                ]
            ],
        ];

        foreach ($categories as $categoryName => $subCategories) {
            $category = new Category();
            $category->setName($categoryName);
            $category->setType($subCategories['types']);
            $manager->persist($category);

            foreach ($subCategories['subCategories'] as $subCategoryName) {
                $subCategory = new Category();
                $subCategory->setName($subCategoryName);
                $subCategory->setType($subCategories['types']);
                $subCategory->setParent($category);
                $manager->persist($subCategory);
            }
        }

        $manager->flush();
    }
}
