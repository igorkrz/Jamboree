<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtistMediaObject;

/**
 * @extends EntityRepository<ArtistMediaObject>
 */
class ArtistMediaObjectRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return ArtistMediaObject::class;
    }
}
