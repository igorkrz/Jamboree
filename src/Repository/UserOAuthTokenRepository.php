<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserOAuthToken;

/**
 * @extends EntityRepository<UserOAuthToken>
 */
class UserOAuthTokenRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return UserOAuthToken::class;
    }
}
