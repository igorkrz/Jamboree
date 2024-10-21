<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Dto\LocationDto;
use App\Entity\Location;
use AutoMapperPlus\AutoMapperInterface;
use InvalidArgumentException;
use ReflectionException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final readonly class LocationFactory extends BaseFactory
{
    public function __construct(
        private AutoMapperInterface $mapper,
        private PropertyAccessorInterface $propertyAccessor,
    ) {
        parent::__construct($this->propertyAccessor, $this->mapper);
    }

    public function create(): Location
    {
        return new Location();
    }

    public function createFromDto(object $dto): Location
    {
        if (!$dto instanceof LocationDto) {
            throw new InvalidArgumentException(sprintf(
                "Expected instance of %s, but got %s",
                LocationDto::class,
                get_class($dto)
            ));
        }

        /** @var Location $location */
        $location = $this->mapper->map($dto, Location::class);

        return $location;
    }

    /**
     * @param string[] $resolvedData
     * @throws ReflectionException
     */
    public function createDtoFromArray(array $resolvedData): LocationDto
    {
        /** @var LocationDto $dto */
        $dto = $this->mapArrayToObject($resolvedData, LocationDto::class);

        return $dto;
    }
}
