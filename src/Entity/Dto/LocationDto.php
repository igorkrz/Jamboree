<?php

declare(strict_types=1);

namespace App\Entity\Dto;

use function sprintf;

class LocationDto
{
    public ?string $venue = null;

    public ?string $city = null;

    public ?string $addressLine = null;

    public ?string $zipCode = null;

    public ?string $country = null;

    public function __toString(): string
    {
        return sprintf(
            "Venue: %s, City: %s, Address: %s, Zip: %s, Country: %s",
            $this->venue ?? 'Unknown',
            $this->city ?? 'Unknown',
            $this->addressLine ?? 'Unknown',
            $this->zipCode ?? 'Unknown',
            $this->country ?? 'Unknown',
        );
    }
}
