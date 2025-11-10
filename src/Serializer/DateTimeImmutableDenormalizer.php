<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DateTimeImmutableDenormalizer implements DenormalizerInterface
{
    private const SUPPORTED_TYPES = [\DateTimeImmutable::class];

    public function getSupportedTypes(?string $format): array
    {
        return [\DateTimeImmutable::class => true];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return in_array($type, self::SUPPORTED_TYPES, true) && is_string($data);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException('Data must be a string');
        }
        
        $date = \DateTimeImmutable::createFromFormat('d/m/Y', $data);
        if (false === $date) {
            throw new \InvalidArgumentException(sprintf('Invalid date format: %s', $data));
        }

        return $date;
    }
}
