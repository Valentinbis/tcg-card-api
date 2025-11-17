<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CardNameTranslatorService
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    public function translate(string $setId, string $number): ?string
    {
        try {
            // Récupère les cartes de l'extension donnée
            $response = $this->httpClient->request('GET', 'https://api.tcgdex.net/v2/fr/sets/' . $setId);
            $data = $response->toArray();
            
            if (!is_array($data) || !isset($data['cards']) || !is_array($data['cards'])) {
                return null;
            }

            foreach ($data['cards'] as $card) {
                if (!is_array($card)) {
                    continue;
                }
                
                $localId = $card['localId'] ?? null;
                if ($localId == $number && isset($card['name']) && is_string($card['name'])) {
                    return $card['name'];
                }
            }
        } catch (\Exception $e) {
            // Si l'extension n'existe pas ou erreur API, retourner null
            return null;
        }

        return null;
    }
}
