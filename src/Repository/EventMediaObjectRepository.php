<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EventMediaObject;
use Symfony\Component\Uid\Ulid;

/**
 * @extends EntityRepository<EventMediaObject>
 */
class EventMediaObjectRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return EventMediaObject::class;
    }
}
