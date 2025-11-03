<?php

namespace App\Tests\Feature\Controller;

use App\Tests\BaseWebTestCase;

final class SecurityControllerTest extends BaseWebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
    }
}
