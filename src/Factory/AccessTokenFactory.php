<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AccessToken;
use Faker\Provider\Uuid;

final readonly class AccessTokenFactory extends BaseFactory
{
    public function create(): AccessToken
    {
        return new AccessToken();
    }

    public function createFromDto(object $dto): AccessToken
    {
        return $this->create();
    }

    public function createDtoFromArray(array $resolvedData): AccessToken
    {
        return $this->create();
    }
}
