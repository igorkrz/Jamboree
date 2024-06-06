<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;

/**
 * @extends EntityRepository<User>
 */
class UserRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return User::class;
    }
}
