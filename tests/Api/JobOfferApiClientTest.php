<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Api\JobOfferApiClient;
use App\Exception\JobOfferApiClientException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class JobOfferApiClientTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $httpClient;
    private ObjectProphecy $logger;
    private JobOfferApiClient $apiClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->prophesize(HttpClientInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->apiClient = new JobOfferApiClient(
            $this->httpClient->reveal(),
            $this->logger->reveal(),
            'https://api.example.com/',
            'client_id',
            'client_secret'
        );
    }

    public function testGetJobOffersSuccess(): void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->toArray()->willReturn(['offer1', 'offer2']);

        $this->httpClient->request('GET', 'https://api.example.com/v3/fr/ads/search?params', [
            'auth_bearer' => 'dummy_token',
        ])->willReturn($response->reveal());

        // Mock ensureBearerToken method to set a dummy token
        $reflection = new \ReflectionClass($this->apiClient);
        $property = $reflection->getProperty('bearerToken');
        $property->setValue($this->apiClient, 'dummy_token');

        $result = $this->apiClient->get('params');

        self::assertSame(['offer1', 'offer2'], $result);
    }

    public function testGetJobOffersApiError(): void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(500);

        $this->httpClient->request('GET', 'https://api.example.com/v3/fr/ads/search?params', [
            'auth_bearer' => 'dummy_token',
        ])->willReturn($response->reveal());

        // Mock ensureBearerToken method to set a dummy token
        $reflection = new \ReflectionClass($this->apiClient);
        $property = $reflection->getProperty('bearerToken');
        $property->setValue($this->apiClient, 'dummy_token');

        $this->expectException(JobOfferApiClientException::class);
        $this->expectExceptionMessage('Error fetching job offers from API. Please try again later.');

        $this->apiClient->get('params');
    }

    public function testGetAccessTokenSuccess(): void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->toArray()->willReturn(['token' => 'dummy_token']);

        $this->httpClient->request('POST', 'https://api.example.com/v3/fr/login', [
            'json' => [
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
            ],
        ])->willReturn($response->reveal());

        $result = $this->apiClient->getAccessToken();

        self::assertSame(['token' => 'dummy_token'], $result);
    }

    public function testGetAccessTokenApiError(): void
    {
        $this->httpClient->request('POST', 'https://api.example.com/v3/fr/login', [
            'json' => [
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
            ],
        ])->willThrow(new TransportException('Error obtaining Bearer Token'));

        $this->expectException(JobOfferApiClientException::class);
        $this->expectExceptionMessage('Error obtaining Bearer Token from API. Please try again later.');

        $this->apiClient->getAccessToken();
    }
}
