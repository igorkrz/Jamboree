<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\CustomEvent;

final readonly class CustomEventFactory extends BaseFactory
{
    public function create(): CustomEvent
    {
        return new CustomEvent();
    }

    public function createFromDto(object $dto): CustomEvent
    {
        return $this->create();
    }

    public function createDtoFromArray(array $resolvedData): CustomEvent
    {
        return $this->create();
    }
}
