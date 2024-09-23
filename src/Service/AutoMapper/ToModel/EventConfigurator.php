<?php

declare(strict_types=1);

namespace App\Service\AutoMapper\ToModel;

use App\Entity\Dto\EventDto;
use App\Entity\Event;
use App\Entity\EventProvider;
use App\Entity\Location;
use App\Repository\EventProviderRepository;
use App\Repository\LocationRepository;
use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\MappingOperation\Operation;
use DateTime;

final class EventConfigurator implements AutoMapperConfiguratorInterface
{
    public function __construct(
        private readonly EventProviderRepository $eventProviderRepository,
        private readonly LocationRepository $locationRepository,
    ) {
    }

    private const FORMATS = [
        'd.m.',
        'd.m.Y.',
        'd.m.Y',
        'd.m.y.',
        'd.m.y',
        'd/m/Y',
    ];
    public function configure(AutoMapperConfigInterface $config): void
    {
        $mapping = $config->registerMapping(EventDto::class, Event::class);
        $mapping->dontSkipConstructor();

        $mapping->forMember('id', Operation::ignore());
        $mapping->forMember('internalCode', fn (EventDto $dto): ?string => $dto->internalCode);
        $mapping->forMember('name', fn (EventDto $dto): ?string => $dto->name);
        $mapping->forMember('description', fn (EventDto $dto): ?string => $dto->description);
        $mapping->forMember('price', fn (EventDto $dto): ?string => $dto->price);
        $mapping->forMember('url', fn (EventDto $dto): ?string => $dto->url);
        $mapping->forMember('imageUrl', fn (EventDto $dto): ?string => $dto->imageUrl);
        $mapping->forMember('provider', function (EventDto $dto): EventProvider {
            $provider = $this->eventProviderRepository->findOneBy(['name' => $dto->provider]);

            if ($provider instanceof EventProvider) {
                return $provider;
            }

            $provider = new EventProvider();
            return $provider->setName($dto->provider);
        });
        $mapping->forMember('location', function (EventDto $dto, AutoMapperInterface $mapper): ?Location {
            /** @var Location $location */
            $location = $mapper->map($dto->location, Location::class);

            $venue = $location->getVenue();

            if ($venue === null) {
                return null;
            }

            $locationInDatabase = $this->locationRepository->findOneBy(['venue' => $venue]);

            if ($locationInDatabase instanceof Location) {
                return $locationInDatabase;
            }

            return $location;
        });
        //        $mapping->forMember('location', function (EventDto $dto): ?Location {
        //
        //            $location = new Location();
        //            $location
        //                ->setVenue(trim($dto->location['venue'], "- ") ?? null)
        //                ->setCity($dto->location['city'] ?? null);
        //
        //            return $location;
        //
        //            return trim($dto->location, "- ");
        //        });
        $mapping->forMember('holdingDate', function (EventDto $dto): ?\DateTimeInterface {
            if ($dto->holdingDate === null) {
                return null;
            }

            $date = str_replace(',', '', $dto->holdingDate);

            foreach (self::FORMATS as $format) {
                $holdingDate = DateTime::createFromFormat($format, $date);

                if ($holdingDate instanceof DateTime) {
                    return $holdingDate;
                }
            }


            return null;
        });
    }
}
