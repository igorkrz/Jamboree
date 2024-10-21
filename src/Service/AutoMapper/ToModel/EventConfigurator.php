<?php

declare(strict_types=1);

namespace App\Service\AutoMapper\ToModel;

use App\Entity\Dto\EventDto;
use App\Entity\Event;
use App\Entity\EventMediaObject;
use App\Entity\EventProvider;
use App\Entity\Location;
use App\Factory\EventFactory;
use App\Factory\EventMediaObjectFactory;
use App\Manager\MediaManager;
use App\Repository\EventProviderRepository;
use App\Repository\EventRepository;
use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\MappingOperation\Operation;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;

final class EventConfigurator implements AutoMapperConfiguratorInterface
{
    private const GOOGLE_BUCKET_URL = 'https://storage.googleapis.com/jamboree-eu';

    public function __construct(
        private readonly EventFactory $eventFactory,
        private readonly EventMediaObjectFactory $eventMediaObjectFactory,
        private readonly EventProviderRepository $eventProviderRepository,
        private readonly EventRepository $eventRepository,
        private readonly MediaManager $mediaManager,
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
        $mapping->withDefaultOperation(Operation::ignore());
        $mapping->beConstructedUsing(
            function (EventDto $dto): Event {
                $event = $this->eventRepository->findOneBy(['internalCode' => $dto->internalCode]);

                if ($event instanceof Event) {
                    return $event;
                }

                return $this->eventFactory->create();
            }
        );
        $mapping->forMember('internalCode', fn (EventDto $dto): ?string => $dto->internalCode);
        $mapping->forMember('name', fn (EventDto $dto): ?string => $dto->name);
        $mapping->forMember('description', fn (EventDto $dto): ?string => $dto->description);
        $mapping->forMember('price', fn (EventDto $dto): ?string => $dto->price);
        $mapping->forMember('url', fn (EventDto $dto): ?string => $dto->url);
        $mapping->forMember('imageUrl', fn (EventDto $dto): ?string => $dto->imageUrl);
        $mapping->forMember('picture', function (EventDto $dto, AutoMapperInterface $mapper, array $context): ?EventMediaObject {
            if ($dto->imageUrl === null) {
                return null;
            }

            $file = $this->mediaManager->createMediaObjectFileFromUrl($dto->imageUrl);

            if (!$file instanceof File) {
                return null;
            }

            /** @var Event $event */
            $event = $context['__destination'];

            if ($event->getPicture() instanceof EventMediaObject) {
                return $event->getPicture();
            }

            $picture = $this->eventMediaObjectFactory->create();
            $picture->setFile($file);

            $picture->setFilePath($this->getStorageFilePath($picture, $file));

            return $picture;
        });
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

            if ($location->getVenue() === null) {
                return null;
            }

            return $location;
        });
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

    private function getStorageFilePath(EventMediaObject $picture, ?File $file = null): ?string
    {
        if (!$file instanceof File) {
            return null;
        }

        return self::GOOGLE_BUCKET_URL . '/images/events/' .
            $picture->getId() . '.' . $file->guessExtension();
    }
}
