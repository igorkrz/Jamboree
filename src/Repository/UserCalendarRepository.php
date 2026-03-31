<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserCalendar;

/**
 * @extends EntityRepository<UserCalendar>
 */
class UserCalendarRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return UserCalendar::class;
    }
}
