<?php

declare(strict_types=1);

namespace App\Service\AutoMapper\ToModel;

use App\Entity\Dto\LocationDto;
use App\Entity\Location;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\MappingOperation\Operation;

final class LocationConfigurator implements AutoMapperConfiguratorInterface
{
    public function configure(AutoMapperConfigInterface $config): void
    {
        $mapping = $config->registerMapping(LocationDto::class, Location::class);
        $mapping->dontSkipConstructor();

        $mapping->forMember('id', Operation::ignore());
        $mapping->forMember('venue', function (LocationDto $dto): ?string {
            if ($dto->venue === null) {
                return null;
            }

            return trim($dto->venue);
        });
        $mapping->forMember('city', fn (LocationDto $dto): ?string => $dto->city);
    }
}
