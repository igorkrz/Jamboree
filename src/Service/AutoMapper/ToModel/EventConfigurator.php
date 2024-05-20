<?php

declare(strict_types=1);

namespace App\Service\AutoMapper\ToModel;

use App\Entity\Dto\EventDto;
use App\Entity\Event;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\MappingOperation\Operation;
use DateTime;

final class EventConfigurator implements AutoMapperConfiguratorInterface
{
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
        $mapping->forMember('location', fn (EventDto $dto): ?string => $dto->location);
        $mapping->forMember('price', fn (EventDto $dto): ?string => $dto->price);
        $mapping->forMember('url', fn (EventDto $dto): ?string => $dto->url);
        $mapping->forMember('imageUrl', fn (EventDto $dto): ?string => $dto->imageUrl);
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
