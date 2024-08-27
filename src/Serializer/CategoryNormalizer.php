<?php
namespace App\Serializer;

use App\Entity\Category;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class CategoryNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const SUPPORTED_TYPES = [Category::class];

    public function getSupportedTypes(?string $format): array
    {
        return [Category::class => true];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return in_array(get_class($data), self::SUPPORTED_TYPES, true);
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        // Normaliser les enfants sans inclure leurs propres enfants
        $normalizedChildren = array_map(function($child) {
            return [
                'value' => $child->getId(),
                'label' => $child->getName(),
                'type' => $child->getType(),
            ];
        }, $object->getChildren()->toArray());

        // Filtrer les enfants normalisés pour exclure ceux qui sont vides
        $filteredChildren = array_filter($normalizedChildren, function($child) {
            return !empty($child['label']);
        });

        $data = [
            'id' => $object->getId(),
            'label' => $object->getName(),
            'type' => $object->getType(),
            'children' => array_values($filteredChildren), // Réindexer les enfants filtrés
        ];

        return $data;
    }
}