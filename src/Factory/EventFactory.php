<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Dto\EventDto;
use App\Entity\Event;
use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\Exception\UnregisteredMappingException;
use InvalidArgumentException;
use ReflectionException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

readonly class EventFactory extends BaseFactory
{
    public function __construct(
        private AutoMapperInterface $mapper,
        private PropertyAccessorInterface $propertyAccessor,
    ) {
        parent::__construct($this->propertyAccessor);
    }

    public function create(): Event
    {
        return new Event();
    }

    /**
     * @throws UnregisteredMappingException|InvalidArgumentException
     */
    public function createFromDto(object $dto): Event
    {
        if (!$dto instanceof EventDto) {
            throw new InvalidArgumentException(sprintf(
                "Expected instance of %s, but got %s",
                EventDto::class,
                get_class($dto)
            ));
        }

        /** @var Event $event */
        $event = $this->mapper->map($dto, Event::class);

        return $event;
    }

    /**
     * @param string[] $resolvedData
     * @throws ReflectionException
     */
    public function createDtoFromArray(array $resolvedData): EventDto
    {
        /** @var EventDto $dto */
        $dto = $this->mapArrayToObject($resolvedData, EventDto::class);

        return $dto;
    }
}
