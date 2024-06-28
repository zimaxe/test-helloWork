<?php

declare(strict_types=1);

namespace App\Interface;

interface JobOfferApiClientInterface
{
    public function get(string $params): array;

    public function getAccessToken(): array;
}
