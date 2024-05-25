<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\UserEvent;

final readonly class UserEventFactory extends BaseFactory
{
    public function create(): UserEvent
    {
        return new UserEvent();
    }

    public function createFromDto(object $dto): UserEvent
    {
        return $this->create();
    }

    public function createDtoFromArray(array $resolvedData): UserEvent
    {
        return $this->create();
    }
}
