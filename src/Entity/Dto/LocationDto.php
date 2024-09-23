<?php

declare(strict_types=1);

namespace App\Entity\Dto;

use Symfony\Component\Uid\Ulid;

class LocationDto
{
    public ?string $venue = null;

    public ?string $city = null;
}
