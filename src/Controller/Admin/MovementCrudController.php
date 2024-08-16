<?php

namespace App\Controller\Admin;

use App\Entity\Movement;
use App\Enums\MovementEnum;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class MovementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Movement::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            NumberField::new('amount'),
            TextField::new('type'),
            // ->hideOnForm(),
            // ChoiceField::new('type')
            //     ->setFormType(EnumType::class)
            //     ->setFormTypeOption('class', MovementEnum::class)
            //     ->setChoices(MovementEnum::cases())
            //     ->onlyOnForms(),
            TextEditorField::new('description'),
            AssociationField::new('user'),
            AssociationField::new('category'),
            // AssociationField::new('category.children'),
            AssociationField::new('recurrence'),
            DateField::new('createdAt')->hideOnForm(),
            DateField::new('updatedAt')->hideOnForm(),
        ];
    }
}
