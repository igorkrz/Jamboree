<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;

final readonly class UserFactory extends BaseFactory
{
    public function create(): User
    {
        return new User();
    }

    public function createFromDto(object $dto): User
    {
        return $this->create();
    }

    public function createDtoFromArray(array $resolvedData): User
    {
        return $this->create();
    }
}
