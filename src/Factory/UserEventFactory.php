<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\UserEvent;

final readonly class UserEventFactory extends BaseFactory
{
    public function create(): object
    {
        return new UserEvent();
    }

    public function createFromDto(object $dto): object
    {
        return $this->create();
    }

    public function createDtoFromArray(array $resolvedData): object
    {
        return $this->create();
    }
}
