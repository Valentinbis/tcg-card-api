<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Service\PriceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PriceController extends AbstractController
{
    public function __construct(
        private PriceService $priceService
    ) {
    }

    /**
     * Récupère les prix d'une carte dans toutes les langues
     */
    #[Route('/api/cards/{cardId}/prices', name: 'api_card_prices', methods: ['GET'])]
    public function getCardPrices(string $cardId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        try {
            $prices = $this->priceService->fetchCardPricesAllLanguages($cardId);

            if (empty($prices)) {
                // Retourner estimation si pas de prix réel
                $estimated = $this->priceService->getEstimatedPrice('Common');
                return $this->json([
                    'cardId' => $cardId,
                    'fr' => $estimated,
                    'en' => $estimated,
                    'jp' => $estimated,
                    'source' => 'estimated',
                    'lastUpdated' => (new \DateTimeImmutable())->format('c'),
                ]);
            }

            $result = ['cardId' => $cardId];
            foreach ($prices as $locale => $price) {
                $result[$locale] = [
                    'marketPrice' => $price->getMarketPrice(),
                    'lowPrice' => $price->getLowPrice(),
                    'highPrice' => $price->getHighPrice(),
                    'averagePrice' => $price->getAveragePrice(),
                ];
            }
            $result['lastUpdated'] = $prices[array_key_first($prices)]->getLastUpdated()->format('c');

            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la récupération des prix'], 500);
        }
    }
}
