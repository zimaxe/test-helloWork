<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Api\JobOfferApiClient;
use App\DTO\JobOfferCollectionDTO;
use App\DTO\JobOfferDTO;
use App\Exception\JobOfferApiClientException;
use App\Exception\JobOfferServiceException;
use App\Interface\JobOfferApiClientInterface;
use App\Service\JobOfferService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class JobOfferServiceTest extends TestCase
{
    private JobOfferApiClientInterface $jobOfferApiClientMock;
    private SerializerInterface $serializerMock;
    private LoggerInterface $loggerMock;

    protected function setUp(): void
    {
        // Créer des mocks pour JobOfferApiClient et SerializerInterface
        $this->jobOfferApiClientMock = $this->createMock(JobOfferApiClient::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
    }

    private function createJobOffersResponseData(): array
    {
        return [
            'data' => [
                'total' => 10,
                'ads' => [
                    ['title' => 'Job 1', 'description' => 'Description 1'],
                    ['title' => 'Job 2', 'description' => 'Description 2'],
                ],
            ],
        ];
    }

    public function testGetJobOffersReturnsJobOfferCollection(): void
    {
        $responseData = $this->createJobOffersResponseData();

        $this->jobOfferApiClientMock->expects(self::once())
            ->method('get')
            ->willReturn($responseData)
        ;

        $jobOfferDTO1 = new JobOfferDTO('Job 1', 'Description 1');
        $jobOfferDTO2 = new JobOfferDTO('Job 2', 'Description 2');

        $this->serializerMock->expects(self::exactly(2))
            ->method('deserialize')
            ->willReturnOnConsecutiveCalls($jobOfferDTO1, $jobOfferDTO2)
        ;

        $jobOfferService = new JobOfferService($this->jobOfferApiClientMock, $this->loggerMock, $this->serializerMock);

        $jobOfferCollection = $jobOfferService->getJobOffers(1);

        self::assertInstanceOf(JobOfferCollectionDTO::class, $jobOfferCollection);
        self::assertCount(2, $jobOfferCollection->getJobOffers());
        self::assertSame(10, $jobOfferCollection->getTotal());
    }

    public function testGetJobOffersThrowsExceptionOnApiClientError(): void
    {
        // Configurer le mock de JobOfferApiClient pour simuler une exception
        $this->jobOfferApiClientMock->expects(self::once())
            ->method('get')
            ->willThrowException(new JobOfferApiClientException('API error'))
        ;

        $jobOfferService = new JobOfferService($this->jobOfferApiClientMock, $this->loggerMock, $this->serializerMock);

        $this->expectException(JobOfferServiceException::class);
        $jobOfferService->getJobOffers(1);
    }

    public function testGetJobOffersHandlesDeserializationError(): void
    {
        $responseData = $this->createJobOffersResponseData();

        $this->jobOfferApiClientMock->expects(self::once())
            ->method('get')
            ->willReturn($responseData)
        ;

        $this->serializerMock->expects(self::once())
            ->method('deserialize')
            ->willThrowException(new RuntimeException('Deserialization error'))
        ;

        $jobOfferService = new JobOfferService($this->jobOfferApiClientMock, $this->loggerMock, $this->serializerMock);

        $this->expectException(JobOfferServiceException::class);
        $jobOfferService->getJobOffers(1);
    }
}
