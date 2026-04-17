<?php

declare(strict_types=1);

namespace App\Service\AutoMapper\ToModel;

use App\Entity\Dto\EventDto;
use App\Entity\Event;
use App\Entity\EventMediaObject;
use App\Entity\EventProvider;
use App\Entity\Location;
use App\Entity\Tag;
use App\Factory\EventFactory;
use App\Factory\EventMediaObjectFactory;
use App\Manager\MediaManager;
use App\Repository\EventProviderRepository;
use App\Repository\EventRepository;
use App\Repository\TagRepository;
use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\MappingOperation\Operation;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;

final class EventConfigurator implements AutoMapperConfiguratorInterface
{
    /** @var string[] */
    private const array FORMATS = [
        'Y-m-d',
        'd.m.',
        'd.m.Y.',
        'd.m.Y',
        'd.m.y.',
        'd.m.y',
        'd/m/Y',
    ];

    public function __construct(
        private readonly EventFactory $eventFactory,
        private readonly EventMediaObjectFactory $eventMediaObjectFactory,
        private readonly EventProviderRepository $eventProviderRepository,
        private readonly EventRepository $eventRepository,
        private readonly TagRepository $tagRepository,
        private readonly MediaManager $mediaManager,
    ) {
    }

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
        $mapping->forMember('isAiEnriched', fn (EventDto $dto): bool => $dto->isAiEnriched);
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
            $picture->setFilePath($this->mediaManager->getStorageFilePath($picture, $file));

            return $picture;
        });
        $mapping->forMember('provider', function (EventDto $dto): EventProvider {
            $providerName = $dto->provider ?? 'Unknown';
            $provider = $this->eventProviderRepository->findOneBy(['name' => $providerName]);

            if ($provider instanceof EventProvider) {
                return $provider;
            }

            $provider = new EventProvider();
            return $provider->setName($providerName);
        });
        $mapping->forMember('location', function (EventDto $dto, AutoMapperInterface $mapper): ?Location {
            /** @var Location $location */
            $location = $mapper->map($dto->location, Location::class);

            if ($location->getVenue() === null) {
                return null;
            }

            return $location;
        });
        $mapping->forMember('holdingDate', function (EventDto $dto): ?DateTimeInterface {
            if ($dto->holdingDate === null) {
                return null;
            }

            if ($dto->holdingDate instanceof DateTimeInterface) {
                return $dto->holdingDate;
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

        /**
         * @return Collection<Tag>
         */
        $tagOperation = function (EventDto $dto): Collection {
            $tags = new ArrayCollection();

            foreach ($dto->tags as $tagName) {
                $tag = $this->tagRepository->findOneBy(['name' => $tagName]);

                if (!$tag instanceof Tag) {
                    $tag = new Tag();
                    $tag->setName($tagName);
                }

                $tags->add($tag);
            }

            return $tags;
        };

        $mapping->forMember('tags', $tagOperation);
    }
}
