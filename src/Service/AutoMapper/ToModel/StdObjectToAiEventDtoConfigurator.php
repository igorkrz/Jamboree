<?php

declare(strict_types=1);

namespace App\Service\AutoMapper\ToModel;

use App\Entity\Dto\AiEventDto;
use App\Entity\Dto\ArtistDto;
use App\Entity\Dto\LocationDto;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use stdClass;

final class StdObjectToAiEventDtoConfigurator implements AutoMapperConfiguratorInterface
{
    public function configure(AutoMapperConfigInterface $config): void
    {
        $config->registerMapping(stdClass::class, AiEventDto::class)
            ->forMember('location', function (stdClass $source): LocationDto {
                $location = new LocationDto();
                $location->venue = $source->location?->venue ?? null;
                $location->city = $source->location?->city ?? null;
                $location->addressLine = $source->location?->addressLine ?? null;
                $location->zipCode = $source->location?->zipCode ?? null;
                $location->country = $source->location?->country ?? null;

                return $location;
            })
            ->forMember('artists', function (stdClass $source): array {
                $artists = $source->artists ?? [];
                if (!is_array($artists)) {
                    return [];
                }

                return array_map(
                    fn (string $artistName) => new ArtistDto($artistName, "Artist extracted via AI"),
                    $artists,
                );
            })
            ->forMember('isAiEnriched', fn (): bool => true);
    }
}
