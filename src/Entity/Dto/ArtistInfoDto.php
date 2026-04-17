<?php

declare(strict_types=1);

namespace App\Entity\Dto;

final readonly class ArtistInfoDto
{
    /**
     * @param string[] $genres
     */
    public function __construct(
        public array $genres = [],
        public ?string $summary = null,
        public ?string $bio = null,
        public ?string $imageUrl = null,
    ) {
    }
}
