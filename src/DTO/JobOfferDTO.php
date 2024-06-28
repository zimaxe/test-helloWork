<?php

declare(strict_types=1);

namespace App\DTO;

readonly class JobOfferDTO
{
    public function __construct(
        private ?string $title = null,
        private ?string $description = null,
        private ?string $company = null,
        private ?string $link = null,
        private ?string $city = null,
        private ?string $salary = null,
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getSalary(): ?string
    {
        return $this->salary;
    }
}
