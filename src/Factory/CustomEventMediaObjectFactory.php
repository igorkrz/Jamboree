<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\CustomEventMediaObject;

final readonly class CustomEventMediaObjectFactory extends BaseFactory
{
    public function create(): CustomEventMediaObject
    {
        return new CustomEventMediaObject();
    }

    public function createFromDto(object $dto): CustomEventMediaObject
    {
        return $this->create();
    }

    public function createDtoFromArray(array $resolvedData): CustomEventMediaObject
    {
        return $this->create();
    }
}
