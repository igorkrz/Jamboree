<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;

/**
 * @extends EntityRepository<Notification>
 */
class NotificationRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return Notification::class;
    }

    public function deleteNotificationsForUser(User $user): void
    {
        $this->createQueryBuilder('n')
            ->delete(Notification::class, 'n')
            ->where('n.user = :user')
            ->setParameter('user', $user->getObjectIdentifier())
            ->getQuery()
            ->execute();
    }
}
