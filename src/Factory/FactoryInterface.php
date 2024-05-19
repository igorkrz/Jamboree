<?php

declare(strict_types=1);

namespace App\Factory;

interface FactoryInterface
{
    public function create(): object;

    public function createFromDto(object $dto): object;

    /**
     * @param string[] $resolvedData
     */
    public function createDtoFromArray(array $resolvedData): object;
}
