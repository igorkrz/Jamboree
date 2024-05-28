<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contract\ResourceInterface;
use App\Entity\Event;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;

/**
 * @extends EntityRepository<Event>
 */
class EventRepository extends EntityRepository
{
    public function getUpcomingEventsQueryBuilder(string $sortField, string $order): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->where('e.holdingDate >= :today')
            ->orWhere('e.holdingDate is null')
            ->orderBy('e.' . $sortField, $order)
            ->setParameter('today', new DateTime());
    }

    protected function getEntityName(): string
    {
        return Event::class;
    }
}
