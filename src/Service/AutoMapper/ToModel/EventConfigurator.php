<?php

declare(strict_types=1);

namespace App\Service\AutoMapper\ToModel;

use App\Entity\Dto\EventDto;
use App\Entity\Event;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\MappingOperation\Operation;

final class EventConfigurator implements AutoMapperConfiguratorInterface
{
    public function configure(AutoMapperConfigInterface $config): void
    {
        $mapping = $config->registerMapping(EventDto::class, Event::class);
        $mapping->dontSkipConstructor();

        $mapping->forMember('id', Operation::ignore());
        $mapping->forMember('internalCode', fn (EventDto $dto): ?string => $dto->internalCode);
        $mapping->forMember('name', fn (EventDto $dto): ?string => $dto->name);
        $mapping->forMember('description', fn (EventDto $dto): ?string => $dto->description);
        $mapping->forMember('location', fn (EventDto $dto): ?string => $dto->location);
        $mapping->forMember('price', fn (EventDto $dto): ?string => $dto->price);
        $mapping->forMember('url', fn (EventDto $dto): ?string => $dto->url);
        $mapping->forMember('imageUrl', fn (EventDto $dto): ?string => $dto->imageUrl);
        $mapping->forMember('holdingDate', function (EventDto $dto): ?\DateTimeInterface {
            if ($dto->holdingDate === null) {
                return null;
            }

            try {
                return new \DateTime($dto->holdingDate);
            } catch (\Exception $e) {
                return null;
            }
        });
    }
}
