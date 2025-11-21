<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Price;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PriceService
{
    private const CACHE_DURATION_HOURS = 24;
    private const SUPPORTED_LOCALES = ['fr', 'en', 'jp'];

    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Récupère les prix d'une carte depuis TCGdex pour une langue spécifique.
     */
    public function fetchCardPrices(string $cardId, string $locale = 'fr'): ?Price
    {
        // Vérifier si on a déjà des prix récents pour cette langue
        $existingPrice = $this->em->getRepository(Price::class)->findOneBy([
            'cardId' => $cardId,
        ]);

        if ($existingPrice && $existingPrice->getLastUpdated() > new \DateTimeImmutable('-'.self::CACHE_DURATION_HOURS.' hours')) {
            return $existingPrice;
        }

        try {
            $response = $this->httpClient->request('GET', "https://api.tcgdex.net/v2/{$locale}/cards/{$cardId}");

            if (200 !== $response->getStatusCode()) {
                $this->logger->warning('API TCGdex returned status {status} for card {cardId} in locale {locale}', [
                    'status' => $response->getStatusCode(),
                    'cardId' => $cardId,
                    'locale' => $locale,
                ]);

                return $existingPrice; // Retourner les anciens prix si API indisponible
            }

            $data = $response->toArray();

            $price = $existingPrice ?: new Price();
            $price->setCardId($cardId);

            // Extraire les prix Cardmarket (toujours en EUR)
            if (isset($data['cardmarket'])) {
                $cardmarket = $data['cardmarket'];

                $marketPrice = $cardmarket['trend'] ?? $cardmarket['avg'] ?? null;
                $lowPrice = $cardmarket['low'] ?? null;
                $highPrice = null; // Cardmarket ne fournit pas de high price direct
                $averagePrice = $cardmarket['avg'] ?? null;

                if (null !== $marketPrice) {
                    $price->setMarketPrice($marketPrice);
                    $price->setLowPrice($lowPrice);
                    $price->setHighPrice($highPrice);
                    $price->setAveragePrice($averagePrice);

                    $price->setLastUpdated(new \DateTimeImmutable());

                    if (!$existingPrice) {
                        $this->em->persist($price);
                    }
                    $this->em->flush();

                    return $price;
                }
            }

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des prix pour {cardId} ({locale}): {message}', [
                'cardId' => $cardId,
                'locale' => $locale,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        }

        return $existingPrice;
    }

    /**
     * Récupère les prix d'une carte dans toutes les langues disponibles.
     */
    public function fetchCardPricesAllLanguages(string $cardId): array
    {
        $prices = [];

        foreach (self::SUPPORTED_LOCALES as $locale) {
            $price = $this->fetchCardPrices($cardId, $locale);
            if ($price) {
                $prices[$locale] = $price;
            }
        }

        return $prices;
    }

    /**
     * Récupère les prix pour plusieurs cartes.
     *
     * @param array<string> $cardIds
     */
    public function fetchMultipleCardPrices(array $cardIds): array
    {
        $prices = [];
        foreach ($cardIds as $cardId) {
            $price = $this->fetchCardPrices($cardId);
            if ($price) {
                $prices[$cardId] = $price;
            }
        }

        return $prices;
    }

    /**
     * Prix estimé basé sur la rareté (fallback).
     */
    public function getEstimatedPrice(string $rarity): array
    {
        $estimates = [
            'Common' => ['marketPrice' => 1.50, 'lowPrice' => 0.50, 'highPrice' => 3.00, 'averagePrice' => 1.25],
            'Uncommon' => ['marketPrice' => 4.00, 'lowPrice' => 1.50, 'highPrice' => 8.00, 'averagePrice' => 3.50],
            'Rare' => ['marketPrice' => 8.00, 'lowPrice' => 3.00, 'highPrice' => 15.00, 'averagePrice' => 7.00],
            'Rare Holo' => ['marketPrice' => 15.00, 'lowPrice' => 5.00, 'highPrice' => 30.00, 'averagePrice' => 12.50],
            'Ultra Rare' => ['marketPrice' => 25.00, 'lowPrice' => 10.00, 'highPrice' => 50.00, 'averagePrice' => 20.00],
            'Secret Rare' => ['marketPrice' => 50.00, 'lowPrice' => 20.00, 'highPrice' => 150.00, 'averagePrice' => 45.00],
            '1st Edition' => ['marketPrice' => 100.00, 'lowPrice' => 50.00, 'highPrice' => 300.00, 'averagePrice' => 90.00],
        ];

        return $estimates[$rarity] ?? $estimates['Common'];
    }
}
