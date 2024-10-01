<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\EventMediaObject;

final readonly class EventMediaObjectFactory extends BaseFactory
{
    public function create(): EventMediaObject
    {
        return new EventMediaObject();
    }

    public function createFromDto(object $dto): EventMediaObject
    {
        return $this->create();
    }

    public function createDtoFromArray(array $resolvedData): EventMediaObject
    {
        return $this->create();
    }
}
