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

    public function translate(string $number): ?string
    {
        // Récupère les cartes de l'extension EV08
        $response = $this->httpClient->request('GET', 'https://api.tcgdex.net/v2/fr/sets/sv08');
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

        return null;
    }
}
