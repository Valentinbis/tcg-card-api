<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Card;

class CardNameTranslatorService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $em
    ) {}

    public function translate(int $number): ?string
    {
        // Récupère les cartes de l'extension EV08
        $response = $this->httpClient->request('GET', 'https://api.tcgdex.net/v2/fr/sets/sv08');
        $data = $response->toArray();

        foreach ($data['cards'] as $card) {
            if ($card['localId'] == $number) {
                return $card['name'];
            }
        }

        return null;
    }
}
