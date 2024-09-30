<?php

namespace App\Tests\Controller;

use App\Controller\API\InfosController;
use App\Tests\BaseWebTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InfosControllerTest extends BaseWebTestCase
{
    private Connection $mockConnection;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the database connection
        $this->mockConnection = $this->createMock(Connection::class);
    }

    public function testIndexReturnsExpectedJsonWhenDatabaseIsConnected(): void
    {
        // Mock the Result object to simulate a successful query
        $mockResult = $this->createMock(Result::class);

        // Simulate a successful database connection
        $this->mockConnection->method('executeQuery')->willReturn($mockResult);

        // Manually instantiate the controller and inject the mock connection
        $controller = new InfosController($this->mockConnection);

        // Simulate a request (if needed)
        $request = Request::create('/api', 'GET');

        // Call the controller action directly
        $response = $controller->index($request);

        // Assert that the response is a 200 OK
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response content
        $responseData = json_decode($response->getContent(), true);
        $this->assertSame([
            'name' => $_ENV['APP_NAME'],
            'env' => $_ENV['APP_ENV'],
            'database' => [
                'connected' => true
            ]
        ], $responseData);
    }

    public function testIndexReturnsExpectedJsonWhenDatabaseIsNotConnected(): void
    {
        // Simulate a failed database connection by throwing an exception
        $this->mockConnection->method('executeQuery')->willThrowException(new \Exception());

        // Manually instantiate the controller and inject the mock connection
        $controller = new InfosController($this->mockConnection);

        // Simulate a request (if needed)
        $request = Request::create('/api', 'GET');

        // Call the controller action directly
        $response = $controller->index($request);

        // Assert that the response is a 200 OK
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response content
        $responseData = json_decode($response->getContent(), true);
        $this->assertSame([
            'name' => $_ENV['APP_NAME'],
            'env' => $_ENV['APP_ENV'],
            'database' => [
                'connected' => false
            ]
        ], $responseData);
    }
}
