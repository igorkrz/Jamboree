<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Artist;

/**
 * @extends EntityRepository<Artist>
 */
class ArtistRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return Artist::class;
    }
}
