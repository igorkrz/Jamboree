<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Location;

/**
 * @extends EntityRepository<Location>
 */
class LocationRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return Location::class;
    }
}
