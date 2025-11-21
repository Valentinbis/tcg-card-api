<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Price;
use App\Service\PriceService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PriceServiceTest extends TestCase
{
    private PriceService $priceService;
    private EntityManagerInterface $em;
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->priceService = new PriceService(
            $this->em,
            $this->httpClient,
            $this->logger
        );
    }

    public function testFetchCardPricesReturnsExistingPriceWhenRecent(): void
    {
        $existingPrice = new Price();
        $existingPrice->setCardId('test-card');
        $existingPrice->setLastUpdated(new \DateTimeImmutable('-12 hours'));

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['cardId' => 'test-card'])
            ->willReturn($existingPrice);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Price::class)
            ->willReturn($repository);

        $result = $this->priceService->fetchCardPrices('test-card');

        $this->assertSame($existingPrice, $result);
    }

    public function testConstantsAreDefined(): void
    {
        $reflection = new \ReflectionClass(PriceService::class);

        $this->assertEquals(24, $reflection->getConstant('CACHE_DURATION_HOURS'));
        $this->assertEquals(['fr', 'en', 'jp'], $reflection->getConstant('SUPPORTED_LOCALES'));
    }
}
