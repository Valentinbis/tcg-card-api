<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $categories = [
            'Transport' => [
                'Accessoires pour venicule',
                'Assurance auto',
                'Carburant',
                'Entretien de véhicule',
                'Location de voiture et moto',
                'Peage et Sationnement',
                'Taxis et VTC',
                'Trains, avions et ferrys',
                'Transports en commun',
                'vélo',
                'Transports-autre'
            ],
            'Shopping et services' => [
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
                'Shopping et services - autre',
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
                'Tabac-Presse'
            ],
            'Santé' => ['Consultation médicale', 'Mutuelle', 'Opticien', 'Pharmacie', 'Santé - autre'],
            'Revenus et rentrées d\'argent' => [
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
            ],
            'Loisirs et vacances' => [
                'Bar',
                'Expo, musée, cinéma',
                'Hôtel',
                'Livres, Magazines',
                'Sport, Gym et Equipement',
                'Vidéo, Musique et jeux',
                'Loisirs et vacances - autre'
            ],
            'Logement - maison' => [
                'Ameublement et appareil',
                'Assurance habitation',
                'Crédit immobilier',
                'Énergie (eau, gaz, électricité, ...',
                'Internet et téléphonie',
                'Loyers et traites',
                'Travaux - Entretien',
                'Logement - autre'
            ],
            'Juridique et administratif' => ['Avocats', 'Frais d\'huissiers', 'Pension alimentaire versée', 'Juridique et administratif - autre'],
            'Impôts et taxes' => ['Impôts sur le revenu', 'ISF', 'Taxes d\'habitation et foncière', 'Impôts et taxes - autre'],
            'Épargne' => ['Epargne logement', 'Epargne retraite', 'Livrets', 'Placement financier', 'Epargne - autre'],
            'Alimentation' => ['Hyper/supermarché', 'Petit commerçant', 'Restaurant', 'Restauration rapide', 'Alimentation - autre'],
            'Banque et assurances' => ['Crédit', 'Crédit consommation', 'Frais bancaires', 'Frais d\'incidents', 'Titres', 'Banque et assurance - autre'],
            'Éducation et famille' => [
                'Assurance scolaire',
                'Fournitures scolaires',
                'Garde d\'enfant',
                'Scolarité et études',
                'Soutien scolaire',
                'Education et Famille - autre'
            ],
        ];

        foreach ($categories as $categoryName => $subCategories) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);

            foreach ($subCategories as $subCategoryName) {
                $subCategory = new Category();
                $subCategory->setName($subCategoryName);
                $subCategory->setParent($category);
                $manager->persist($subCategory);
            }
        }

        $manager->flush();
    }
}
