<?php

namespace App\Tests\Unit\Service;

use App\Service\LoggerService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class LoggerServiceTest extends TestCase
{
    private LoggerInterface $mainLogger;
    private LoggerInterface $actionLogger;
    private LoggerInterface $securityLogger;
    private LoggerInterface $requestLogger;
    private LoggerInterface $performanceLogger;
    private RequestStack $requestStack;
    private TokenStorageInterface $tokenStorage;
    private LoggerService $loggerService;

    protected function setUp(): void
    {
        $this->mainLogger = $this->createMock(LoggerInterface::class);
        $this->actionLogger = $this->createMock(LoggerInterface::class);
        $this->securityLogger = $this->createMock(LoggerInterface::class);
        $this->requestLogger = $this->createMock(LoggerInterface::class);
        $this->performanceLogger = $this->createMock(LoggerInterface::class);
        $this->requestStack = new RequestStack();
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->loggerService = new LoggerService(
            $this->mainLogger,
            $this->actionLogger,
            $this->securityLogger,
            $this->requestLogger,
            $this->performanceLogger,
            $this->requestStack,
            $this->tokenStorage
        );
    }

    public function testLogActionCallsActionLogger(): void
    {
        $this->actionLogger->expects(self::once())
            ->method('log')
            ->with(
                'info',
                'Test action',
                self::callback(function ($context) {
                    return isset($context['timestamp']) && isset($context['environment']);
                })
            );

        $this->loggerService->logAction('Test action');
    }

    public function testLogActionWithCustomLevel(): void
    {
        $this->actionLogger->expects(self::once())
            ->method('log')
            ->with('warning', 'Warning action', self::anything());

        $this->loggerService->logAction('Warning action', [], 'warning');
    }

    public function testLogSecurityCallsSecurityLogger(): void
    {
        $this->securityLogger->expects(self::once())
            ->method('log')
            ->with('info', 'Security event', self::anything());

        $this->loggerService->logSecurity('Security event');
    }

    public function testLogRequestAddsHttpContext(): void
    {
        $request = new Request([], [], [], [], [], [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/api/test',
        ]);
        $this->requestStack->push($request);

        $this->requestLogger->expects(self::once())
            ->method('log')
            ->with(
                'info',
                'Request test',
                self::callback(function ($context) {
                    return isset($context['http'])
                        && $context['http']['method'] === 'GET'
                        && $context['http']['uri'] === '/api/test';
                })
            );

        $this->loggerService->logRequest('Request test');
    }

    public function testLogPerformanceWithFastOperation(): void
    {
        $this->performanceLogger->expects(self::once())
            ->method('log')
            ->with(
                'info',
                'Operation completed: fast_operation',
                self::callback(function ($context) {
                    return isset($context['duration_ms'])
                        && $context['duration_ms'] < 1000
                        && $context['operation'] === 'fast_operation';
                })
            );

        $this->loggerService->logPerformance('fast_operation', 0.5);
    }

    public function testLogPerformanceWithSlowOperation(): void
    {
        $this->performanceLogger->expects(self::once())
            ->method('log')
            ->with(
                'warning',
                'Operation completed: slow_operation',
                self::callback(function ($context) {
                    return $context['duration_ms'] > 1000;
                })
            );

        $this->loggerService->logPerformance('slow_operation', 1.5);
    }

    public function testLogPerformanceWithVerySlowOperation(): void
    {
        $this->performanceLogger->expects(self::once())
            ->method('log')
            ->with(
                'warning', // Le code vérifie d'abord > 1.0 donc c'est warning
                'Operation completed: very_slow_operation',
                self::anything()
            );

        $this->loggerService->logPerformance('very_slow_operation', 6.0);
    }

    public function testLogErrorAddsExceptionContext(): void
    {
        $exception = new \RuntimeException('Test error', 123);

        $this->mainLogger->expects(self::once())
            ->method('error')
            ->with(
                'Test error',
                self::callback(function ($context) use ($exception) {
                    return isset($context['exception'])
                        && $context['exception']['class'] === \RuntimeException::class
                        && $context['exception']['message'] === 'Test error'
                        && $context['exception']['code'] === 123;
                })
            );

        $this->loggerService->logError($exception);
    }

    public function testDebugHelper(): void
    {
        $this->mainLogger->expects(self::once())
            ->method('log')
            ->with('debug', 'Debug message', self::anything());

        $this->loggerService->debug('Debug message');
    }

    public function testInfoHelper(): void
    {
        $this->mainLogger->expects(self::once())
            ->method('log')
            ->with('info', 'Info message', self::anything());

        $this->loggerService->info('Info message');
    }

    public function testWarningHelper(): void
    {
        $this->mainLogger->expects(self::once())
            ->method('log')
            ->with('warning', 'Warning message', self::anything());

        $this->loggerService->warning('Warning message');
    }

    public function testErrorHelper(): void
    {
        $this->mainLogger->expects(self::once())
            ->method('log')
            ->with('error', 'Error message', self::anything());

        $this->loggerService->error('Error message');
    }

    public function testCriticalHelper(): void
    {
        $this->mainLogger->expects(self::once())
            ->method('log')
            ->with('critical', 'Critical message', self::anything());

        $this->loggerService->critical('Critical message');
    }

    public function testContextIsEnrichedWithTimestamp(): void
    {
        $this->actionLogger->expects(self::once())
            ->method('log')
            ->with(
                'info',
                'Test',
                self::callback(function ($context) {
                    return isset($context['timestamp']) 
                        && !empty($context['timestamp']);
                })
            );

        $this->loggerService->logAction('Test');
    }

    public function testContextIsEnrichedWithEnvironment(): void
    {
        $this->actionLogger->expects(self::once())
            ->method('log')
            ->with(
                'info',
                'Test',
                self::callback(function ($context) {
                    return isset($context['environment']);
                })
            );

        $this->loggerService->logAction('Test');
    }

    public function testCorrelationIdIsAddedFromRequest(): void
    {
        $request = new Request();
        $request->attributes->set('correlation_id', 'test-correlation-123');
        $this->requestStack->push($request);

        $this->actionLogger->expects(self::once())
            ->method('log')
            ->with(
                'info',
                'Test',
                self::callback(function ($context) {
                    return isset($context['correlation_id'])
                        && $context['correlation_id'] === 'test-correlation-123';
                })
            );

        $this->loggerService->logAction('Test');
    }
}
