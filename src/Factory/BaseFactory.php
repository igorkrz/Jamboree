<?php

declare(strict_types=1);

namespace App\Factory;

use ReflectionException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract readonly class BaseFactory implements FactoryInterface
{
    public function __construct(private PropertyAccessorInterface $propertyAccessor)
    {
    }

    abstract public function create(): object;
    abstract public function createFromDto(object $dto): object;

    abstract public function createDtoFromArray(array $resolvedData): object;

    /**
     * @template T
     * @param string[] $resolvedData
     * @param class-string<T> $targetClass
     * @throws ReflectionException
     */
    public function mapArrayToObject(array $resolvedData, string $targetClass): object
    {
        $ref = new \ReflectionClass($targetClass);
        $fields = $ref->getProperties();

        $dto = new $targetClass();

        foreach ($fields as $field) {
            $fieldName = $field->getName();

            if (isset($resolvedData[$fieldName])) {
                $this->propertyAccessor->setValue($dto, $fieldName, $resolvedData[$fieldName]);
            }
        }

        return $dto;
    }
}
