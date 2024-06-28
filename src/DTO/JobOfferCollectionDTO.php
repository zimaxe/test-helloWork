<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

class JobOfferCollectionDTO
{
    #[SerializedName('total')]
    private int $total;

    #[SerializedName('ads')]
    private array $jobOffers;

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getJobOffers(): array
    {
        return $this->jobOffers;
    }

    public function setJobOffers(array $jobOffers): void
    {
        $this->jobOffers = $jobOffers;
    }
}
