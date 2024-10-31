<?php

declare(strict_types=1);

namespace App\Service\AutoMapper\ToModel;

use App\Entity\Dto\LocationDto;
use App\Entity\Location;
use App\Factory\LocationFactory;
use App\Repository\LocationRepository;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\MappingOperation\Operation;

final readonly class LocationConfigurator implements AutoMapperConfiguratorInterface
{
    public function __construct(
        private LocationFactory $locationFactory,
        private LocationRepository $locationRepository,
    ) {
    }

    public function configure(AutoMapperConfigInterface $config): void
    {
        $mapping = $config->registerMapping(LocationDto::class, Location::class);
        $mapping->withDefaultOperation(Operation::ignore());
        $mapping->beConstructedUsing(
            function (LocationDto $dto): Location {
                $location = $this->locationRepository->findOneBy(['venue' => $dto->venue]);

                if ($location instanceof Location) {
                    return $location;
                }

                return $this->locationFactory->create();
            }
        );
        $mapping->forMember('venue', fn (LocationDto $dto): ?string => $dto->venue);
        $mapping->forMember('city', fn (LocationDto $dto): ?string => $dto->city);
        $mapping->forMember('addressLine', fn (LocationDto $dto): ?string => $dto->addressLine);
        $mapping->forMember('zipCode', fn (LocationDto $dto): ?string => $dto->zipCode);
        $mapping->forMember('country', fn (LocationDto $dto): ?string => $dto->country);
    }
}
