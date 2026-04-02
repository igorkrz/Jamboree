<?php

declare(strict_types=1);

namespace App\Entity\Dto;

use DateTimeInterface;

class EventDto
{
    public ?string $internalCode = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?LocationDto $location = null;

    public ?string $price = null;

    public ?string $url = null;

    public ?string $imageUrl = null;

    public string|DateTimeInterface|null $holdingDate = null;

    public ?string $provider = null;

    /**
     * @var string[]
     */
    public array $tags = [];

    /**
     * @var string[]
     */
    public array $artists = [];
}
