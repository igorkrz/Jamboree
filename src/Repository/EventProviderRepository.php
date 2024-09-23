<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EventProvider;

/**
 * @extends EntityRepository<EventProvider>
 */
class EventProviderRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return EventProvider::class;
    }
}
