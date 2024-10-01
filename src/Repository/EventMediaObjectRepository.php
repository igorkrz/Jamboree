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
    public function findByEventUuid(Ulid $ulid): ?EventMediaObject
    {
        return $this->createQueryBuilder('emo')
            ->where('emo.fileName LIKE :ulid')
            ->setParameter('ulid', $ulid . '%')
            ->getQuery()
            ->getOneOrNullResult();
    }

    protected function getEntityName(): string
    {
        return EventMediaObject::class;
    }
}
