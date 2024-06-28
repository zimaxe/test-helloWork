<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\JobOfferCollectionDTO;
use App\DTO\JobOfferDTO;
use App\Exception\JobOfferServiceException;
use App\Interface\JobOfferApiClientInterface;
use App\Interface\JobOfferServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

readonly class JobOfferService implements JobOfferServiceInterface
{
    public function __construct(
        private JobOfferApiClientInterface $jobOfferApiClient,
        private LoggerInterface $logger,
        private SerializerInterface $serializer,
    ) {
    }

    /** @throws \Exception */
    public function getJobOffers(int $page, ?string $city = null): JobOfferCollectionDTO
    {
        try {
            $params = $this->getParams($page, $city);
            $responseData = $this->jobOfferApiClient->get($params);

            return $this->processJobOffers($responseData);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching job offers', ['exception' => $e]);
            throw new JobOfferServiceException('Error fetching job offers. Please try again later.', $e->getCode(), $e);
        }
    }

    private function processJobOffers($responseData): JobOfferCollectionDTO
    {
        $jobOffers = [];

        if (!isset($responseData['data']['total'], $responseData['data']['ads']) || !\is_array($responseData['data']['ads'])) {
            throw new \InvalidArgumentException('Invalid response data: missing required keys.');
        }

        $jobOfferCollection = new JobOfferCollectionDTO();
        $jobOfferCollection->setTotal($responseData['data']['total']);

        foreach ($responseData['data']['ads'] as $offerData) {
            $jobOffer = $this->serializer->deserialize(json_encode($offerData), JobOfferDTO::class, 'json');
            $jobOffers[] = $jobOffer;
        }

        $jobOfferCollection->setJobOffers($jobOffers);

        return $jobOfferCollection;
    }

    private function getParams(int $page, ?string $city): string
    {
        return http_build_query([
            'page' => $page,
            'limit' => $offset ?? 9,
            'where' => $city ?? 'Bordeaux',
        ]);
    }
}
