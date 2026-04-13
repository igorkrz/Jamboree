<?php

declare(strict_types=1);

namespace App\Service\AutoMapper\ToModel;

use App\Entity\Dto\AiEventDto;
use App\Entity\Dto\EventDto;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\MappingOperation\Operation;

final class AiEventDtoToEventDtoConfigurator implements AutoMapperConfiguratorInterface
{
    public function configure(AutoMapperConfigInterface $config): void
    {
        $config->registerMapping(AiEventDto::class, EventDto::class)
            ->forMember('name', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return $source->name ?? $eventDto->name;
            })
            ->forMember('description', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return $source->description ?? $eventDto->description;
            })
            ->forMember('holdingDate', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return $source->holdingDate ?? $eventDto->holdingDate;
            })
            ->forMember('isAiEnriched', Operation::fromProperty('isAiEnriched'))
            ->forMember('location', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                if ($source->location === null) {
                    return $eventDto->location;
                }

                if ($eventDto->location === null) {
                    return $source->location;
                }

                $source->location->addressLine ??= $eventDto->location->addressLine;
                $source->location->zipCode ??= $eventDto->location->zipCode;
                $source->location->country ??= $eventDto->location->country;

                return $source->location;
            })
            ->forMember('artists', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return !empty($source->artists) ? $source->artists : $eventDto->artists;
            })
            ->forMember('internalCode', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return $eventDto->internalCode;
            })
            ->forMember('url', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return $eventDto->url;
            })
            ->forMember('imageUrl', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return $eventDto->imageUrl;
            })
            ->forMember('price', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return $eventDto->price;
            })
            ->forMember('provider', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return $eventDto->provider;
            })
            ->forMember('tags', function (AiEventDto $source, $destination, array $context) {
                $eventDto = $context['__destination'] ?? $destination;
                return $eventDto->tags;
            });
    }
}
