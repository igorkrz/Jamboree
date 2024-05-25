<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;

final readonly class UserFactory extends BaseFactory
{
    public function create(): object
    {
        return new User();
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
