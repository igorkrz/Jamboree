<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\ArtistMediaObject;

final readonly class ArtistMediaObjectFactory extends BaseFactory
{
    public function create(): ArtistMediaObject
    {
        return new ArtistMediaObject();
    }

    public function createFromDto(object $dto): ArtistMediaObject
    {
        return $this->create();
    }

    public function createDtoFromArray(array $resolvedData): ArtistMediaObject
    {
        return $this->create();
    }
}
