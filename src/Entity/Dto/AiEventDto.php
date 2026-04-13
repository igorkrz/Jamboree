<?php

declare(strict_types=1);

namespace App\Entity\Dto;

use DateTimeInterface;

class AiEventDto
{
    public ?string $name = null;

    public ?string $description = null;

    public ?LocationDto $location = null;

    public string|DateTimeInterface|null $holdingDate = null;

    /**
     * @var ArtistDto[]
     */
    public array $artists = [];

    public bool $isAiEnriched = true;
}
