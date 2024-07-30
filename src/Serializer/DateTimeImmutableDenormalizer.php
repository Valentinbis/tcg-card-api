<?php
namespace App\Serializer;

use DateTimeImmutable;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DateTimeImmutableDenormalizer implements DenormalizerInterface
{
    private const SUPPORTED_TYPES = [DateTimeImmutable::class];

    public function getSupportedTypes(?string $format): array
    {
        return [DateTimeImmutable::class => true];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return in_array($type, self::SUPPORTED_TYPES, true) && is_string($data);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $date = DateTimeImmutable::createFromFormat('d/m/Y', $data);
        if ($date === false) {
            throw new \InvalidArgumentException(sprintf('Invalid date format: %s', $data));
        }
        return $date;
    }
}