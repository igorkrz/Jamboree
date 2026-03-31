<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomEventMediaObject;

/**
 * @extends EntityRepository<CustomEventMediaObject>
 */
class CustomEventMediaObjectRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return CustomEventMediaObject::class;
    }
}
