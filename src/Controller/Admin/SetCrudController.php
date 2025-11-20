<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Set;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Set::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id'),
            TextField::new('name', 'Nom'),
            TextField::new('series', 'Série'),
            DateTimeField::new('releaseDate', 'Date de sortie'),
            NumberField::new('printedTotal', 'Total imprimé'),
            NumberField::new('total', 'Total'),
            TextField::new('ptcgoCode', 'Code PTCGO'),
            DateTimeField::new('updatedAt', 'Dernière mise à jour')->hideOnForm(),
        ];

        // Afficher les images dans l'index et le détail
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            $fields[] = Field::new('symbolImage', 'Symbole')->setTemplatePath('admin/set_symbol_image.html.twig');
            $fields[] = Field::new('logoImage', 'Logo')->setTemplatePath('admin/set_logo_image.html.twig');
        }
        $fields[] = AssociationField::new('cards', 'Cartes')
            ->hideOnForm()
            ->setCrudController(CardCrudController::class);

        return $fields;
    }
}
