<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contract\ResourceInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository as BaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T of ResourceInterface
 *
 * @template-implements RepositoryInterface<T>
 *
 * @extends BaseEntityRepository<T>
 */
class EntityRepository extends BaseEntityRepository implements RepositoryInterface
{
    use ResourceRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, $this->getEntityName());
    }
}
