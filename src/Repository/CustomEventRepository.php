<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomEvent;
use DateTime;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends EntityRepository<CustomEvent>
 */
class CustomEventRepository extends EntityRepository
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
        return CustomEvent::class;
    }
}
