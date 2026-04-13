<?php

declare(strict_types=1);

namespace App\Entity\Dto;

use function sprintf;

class ArtistDto
{
    public ?string $name = null;

    public ?string $description = null;

    public function __construct(?string $name = null, ?string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function __toString(): string
    {
        return sprintf(
            "Name: %s, Description: %s",
            $this->name ?? 'Unknown',
            $this->description ?? 'No description',
        );
    }
}
