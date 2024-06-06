<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomEvent;
use App\Entity\Event;
use DateTime;
use Doctrine\ORM\QueryBuilder;

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

    public function getAllUpcomingEventsQueryBuilder(string $sortField, string $order): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->leftJoin(CustomEvent::class, 'ce', 'WITH')
            ->where('e.holdingDate >= :today')
            ->orWhere('e.holdingDate is null')
            ->orWhere('ce.holdingDate >= :today')
            ->orWhere('ce.holdingDate is null')
            ->orderBy('e.' . $sortField, $order)
            ->addOrderBy('ce.' . $sortField, $order)
            ->setParameter('today', new DateTime());
    }

    protected function getEntityName(): string
    {
        return Event::class;
    }
}
