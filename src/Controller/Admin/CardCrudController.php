<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Card;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @extends AbstractCrudController<Card>
 */
class CardCrudController extends AbstractCrudController implements EventSubscriberInterface
{
    public static function getEntityFqcn(): string
    {
        return Card::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('nameFr'),
            TextField::new('number'),
            TextField::new('rarity'),
            AssociationField::new('set'),
            NumberField::new('hp'),
        ];

        // Pour l'index et le détail, afficher l'image existante avec template personnalisé
        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            $fields[] = Field::new('smallImage')->setLabel('Image')->setTemplatePath('admin/card_image.html.twig');
        }

        // Pour l'édition, afficher l'image actuelle avec template personnalisé
        if (Crud::PAGE_EDIT === $pageName) {
            $fields[] = Field::new('smallImage')
                ->setLabel('Image actuelle')
                ->setTemplatePath('admin/card_image.html.twig')
                ->setHelp('Image actuelle de la carte. Vous pouvez télécharger une nouvelle image ci-dessous.');
        }

        // Pour les formulaires, permettre l'upload d'une nouvelle image (désactivé pour utiliser URLs externes)
        // if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
        //     $fields[] = ImageField::new('uploadedImage')
        //         ->setLabel('Nouvelle image')
        //         ->setBasePath('')
        //         ->setUploadDir('public/images/cards/small')
        //         ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
        //         ->setRequired(false)
        //         ->setHelp('Téléchargez une nouvelle image');
        // }

        return $fields;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'handleImageUpload',
            BeforeEntityUpdatedEvent::class => 'handleImageUpload',
        ];
    }

    /**
     * @param BeforeEntityPersistedEvent<Card>|BeforeEntityUpdatedEvent<Card> $event
     */
    public function handleImageUpload(BeforeEntityPersistedEvent|BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof Card) {
            return;
        }

        // Si une nouvelle image a été uploadée
        $uploadedImagePath = $entity->getUploadedImage();
        if (!is_string($uploadedImagePath)) {
            return;
        }

        // Mettre à jour l'array images
        $images = $entity->getImages() ?? [];
        if (!is_array($images)) {
            $images = [];
        }
        $images['small'] = $uploadedImagePath;
        $images['large'] = str_replace('/small/', '/large/', $uploadedImagePath); // Même image pour l'instant
        $entity->setImages($images);
    }
}
