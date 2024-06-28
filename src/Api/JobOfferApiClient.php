<?php

declare(strict_types=1);

namespace App\Api;

use App\Exception\JobOfferApiClientException;
use App\Interface\JobOfferApiClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JobOfferApiClient implements JobOfferApiClientInterface
{
    private const API_VERSION = 'v3';
    private const API_LANGUAGE = 'fr';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $apiUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private ?string $bearerToken = null
    ) {
    }

    /**
     * @throws JobOfferApiClientException
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function get(string $params): array
    {
        $this->ensureBearerToken();

        try {
            $response = $this->httpClient->request('GET', sprintf('%s/ads/search?%s', $this->formatApiUrl(), $params), [
                'auth_bearer' => $this->bearerToken,
            ]);

            $statusCode = $response->getStatusCode();

            if (200 !== $statusCode) {
                throw new JobOfferApiClientException('Failed to fetch job offers. API returned status code: '.$statusCode);
            }

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Error fetching job offers from API', ['exception' => $e]);
            throw new JobOfferApiClientException('Error fetching job offers from API. Please try again later.', $e->getCode(), $e);
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws JobOfferApiClientException
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getAccessToken(): array
    {
        try {
            $response = $this->httpClient->request('POST', sprintf('%s/login', $this->formatApiUrl()), [
                'json' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Error obtaining Bearer Token from API', ['exception' => $e]);
            throw new JobOfferApiClientException('Error obtaining Bearer Token from API. Please try again later.');
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws JobOfferApiClientException
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function ensureBearerToken(): void
    {
        if (null === $this->bearerToken) {
            try {
                $tokenData = $this->getAccessToken();
                $this->bearerToken = $tokenData['token'] ?? null;

                if (null === $this->bearerToken) {
                    throw new JobOfferApiClientException('Failed to obtain Bearer Token from API.');
                }
            } catch (\Exception $e) {
                $this->logger->error('Error obtaining Bearer Token from API', ['exception' => $e]);
                throw new JobOfferApiClientException('Error obtaining Bearer Token from API. Please try again later.');
            }
        }
    }

    private function formatApiUrl(): string
    {
        $apiBaseUrl = rtrim($this->apiUrl, '/');

        return sprintf('%s/%s/%s', $apiBaseUrl, self::API_VERSION, self::API_LANGUAGE);
    }
}
