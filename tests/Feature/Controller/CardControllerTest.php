<?php

namespace App\Tests\Feature\Controller;

use App\Tests\BaseWebTestCase;

final class CardControllerTest extends BaseWebTestCase
{
    public function testGetCards(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/cards');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
        self::assertArrayHasKey('data', $data);
        self::assertArrayHasKey('pagination', $data);
    }

    public function testGetCardsRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cards');

        self::assertResponseStatusCodeSame(401);
    }
}
