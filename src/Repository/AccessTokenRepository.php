<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccessToken;

/**
 * @extends EntityRepository<AccessToken>
 */
class AccessTokenRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return AccessToken::class;
    }
}
