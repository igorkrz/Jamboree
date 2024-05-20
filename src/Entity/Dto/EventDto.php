<?php

declare(strict_types=1);

namespace App\Entity\Dto;

use Symfony\Component\Uid\Ulid;

class EventDto
{
    public ?string $internalCode = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $location = null;

    public ?string $price = null;

    public ?string $url = null;

    public ?string $imageUrl = null;

    public ?string $holdingDate = null;
}
