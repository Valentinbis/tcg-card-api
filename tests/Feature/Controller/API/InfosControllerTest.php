<?php

namespace App\Tests\Feature\Controller\API;

use App\Tests\BaseWebTestCase;

final class InfosControllerTest extends BaseWebTestCase
{
    public function testHealthCheckReturnsSuccessWithDatabaseConnected(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        
        self::assertArrayHasKey('name', $data);
        self::assertArrayHasKey('env', $data);
        self::assertArrayHasKey('database', $data);
        self::assertArrayHasKey('connected', $data['database']);
        self::assertTrue($data['database']['connected']);
    }

    public function testHealthCheckReturnsAppInfo(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api');

        $data = json_decode($client->getResponse()->getContent(), true);
        
        self::assertSame('test', $data['env']);
    }
}
