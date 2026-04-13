<?php

declare(strict_types=1);

namespace App\Entity\Dto;

use DateTimeInterface;

use function implode;
use function sprintf;

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
     * @var ArtistDto[]
     */
    public array $artists = [];

    public bool $isAiEnriched = false;

    public function __toString(): string
    {
        return sprintf(
            "Event Name: %s\nDescription: %s\nLocation: %s\nDate: %s\nArtists: %s",
            $this->name ?? 'Unknown',
            $this->description ?? 'No description',
            $this->location,
            $this->holdingDate instanceof DateTimeInterface ? $this->holdingDate->format('Y-m-d') : $this->holdingDate,
            implode(', ', $this->artists),
        );
    }
}
