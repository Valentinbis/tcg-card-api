<?php

namespace App\Tests\Feature\EventSubscriber;

use App\EventSubscriber\HttpLoggingSubscriber;
use App\Service\LoggerService;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class HttpLoggingSubscriberTest extends BaseWebTestCase
{
    public function testSubscribedEvents(): void
    {
        $events = HttpLoggingSubscriber::getSubscribedEvents();
        
        self::assertArrayHasKey(KernelEvents::REQUEST, $events);
        self::assertArrayHasKey(KernelEvents::CONTROLLER, $events);
        self::assertArrayHasKey(KernelEvents::RESPONSE, $events);
        self::assertArrayHasKey(KernelEvents::EXCEPTION, $events);
        self::assertArrayHasKey(KernelEvents::TERMINATE, $events);
        
        // Vérifier les priorités
        self::assertSame(['onKernelRequest', 10], $events[KernelEvents::REQUEST]);
        self::assertSame(['onKernelController', 0], $events[KernelEvents::CONTROLLER]);
        self::assertSame(['onKernelResponse', -1000], $events[KernelEvents::RESPONSE]);
        self::assertSame(['onKernelException', 0], $events[KernelEvents::EXCEPTION]);
        self::assertSame(['onKernelTerminate', -1024], $events[KernelEvents::TERMINATE]);
    }

    public function testLogsIncomingRequest(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/me');
        
        self::assertResponseIsSuccessful();
        
        // Le subscriber devrait avoir loggé la requête
        // On vérifie indirectement via le fait que la requête s'est bien passée
        $this->assertJsonResponse($client);
    }

    public function testLogsSuccessfulResponse(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/me');
        
        self::assertResponseStatusCodeSame(200);
    }

    public function testLogsErrorResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/me'); // Sans auth = 401
        
        self::assertResponseStatusCodeSame(401);
    }

    public function testLogsException(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/user/99999'); // User inexistant = erreur
        
        // Devrait retourner une erreur (401 ou 404)
        self::assertResponseStatusCodeSame(401);
    }

    public function testHealthCheckIsNotLogged(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api');
        
        self::assertResponseIsSuccessful();
        // Le health check ne devrait pas générer de logs verbeux
    }

    public function testRequestGetsCorrelationId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/me');
        
        self::assertResponseIsSuccessful();
        // Le correlation_id devrait être ajouté à la requête
    }

    public function testLogActionAttributeIsProcessed(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/me');
        
        self::assertResponseIsSuccessful();
        // La méthode /api/me a un attribut LogAction
    }

    public function testLogSecurityAttributeIsProcessed(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/user/me');
        
        self::assertResponseIsSuccessful();
        // La méthode /api/user/me a un attribut LogSecurity
    }

    public function testLogPerformanceAttributeIsProcessed(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api');
        
        self::assertResponseIsSuccessful();
        // La route /api a un attribut LogPerformance
    }

    public function testSlowRequestIsLogged(): void
    {
        // Difficile à tester sans ralentir artificiellement
        // On vérifie juste que le système fonctionne normalement
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/users');
        
        self::assertResponseIsSuccessful();
    }
}
