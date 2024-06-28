<?php

declare(strict_types=1);

namespace App\Interface;

use App\DTO\JobOfferCollectionDTO;

interface JobOfferServiceInterface
{
    public function getJobOffers(int $page, ?string $city = null): JobOfferCollectionDTO;
}
