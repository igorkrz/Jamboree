<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;

/**
 * @extends EntityRepository<Tag>
 */
class TagRepository extends EntityRepository
{
    protected function getEntityName(): string
    {
        return Tag::class;
    }
}
