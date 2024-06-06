<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEvent;
use DateTime;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends EntityRepository<UserEvent>
 */
class UserEventRepository extends EntityRepository
{
    /**
     * @return UserEvent[]
     */
    public function getUpcomingEvents(User $user): array
    {
        return $this->createQueryBuilder('ue')
            ->leftJoin('ue.event', 'e')
            ->leftJoin('ue.customEvent', 'ce')
            ->where('ue.user = :user')
//            ->where('e.holdingDate >= :today OR e.holdingDate is null')
//            ->orWhere('ce.holdingDate >= :today OR ce.holdingDate is null')
//            ->setParameter('today', new DateTime())
            ->setParameter('user', $user->getId()->toRfc4122())
            ->getQuery()
            ->getResult();
    }

    public function getUpcomingEventsQueryBuilder(string $sortField, string $order): QueryBuilder
    {
        return $this->createQueryBuilder('ue')
            ->leftJoin('ue.event', 'e')
            ->leftJoin('ue.customEvent', 'ce')
            ->where('e.holdingDate >= :today OR e.holdingDate is null')
            ->orWhere('ce.holdingDate >= :today OR ce.holdingDate is null')
            ->orderBy('e.' . $sortField, $order)
            ->addOrderBy('ce.' . $sortField, $order)
            ->setParameter('today', new DateTime());
    }

    protected function getEntityName(): string
    {
        return UserEvent::class;
    }
}
