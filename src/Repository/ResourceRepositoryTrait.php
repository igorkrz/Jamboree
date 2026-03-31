<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use App\Entity\Contract\ResourceInterface;

/**
 * @method EntityManagerInterface getEntityManager()
 * @method ClassMetadata          getClassMetadata()
 * @method QueryBuilder           createQueryBuilder(string $alias, string $indexBy = null)
 * @method ?object                find($id, $lockMode = null, $lockVersion = null)
 */
trait ResourceRepositoryTrait
{
    public function add(ResourceInterface $resource): void
    {
        $this->getEntityManager()->persist($resource);
        $this->getEntityManager()->flush();
    }

    public function remove(ResourceInterface $resource): void
    {
        if (null !== $this->find($resource->getId())) {
            $this->getEntityManager()->remove($resource);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return iterable<int, ResourceInterface>
     */
    public function createPaginator(array $criteria = [], array $sorting = []): iterable
    {
        $queryBuilder = $this->createQueryBuilder('o');

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        return $this->getPaginator($queryBuilder);
    }

    /**
     * @return Pagerfanta<ResourceInterface>
     */
    protected function getPaginator(QueryBuilder $queryBuilder): Pagerfanta
    {
        return new Pagerfanta(new QueryAdapter($queryBuilder, true, false));
    }

    /**
     * @param ResourceInterface[] $objects
     * @return Pagerfanta<ResourceInterface>
     */
    protected function getArrayPaginator(array $objects): Pagerfanta
    {
        return new Pagerfanta(new ArrayAdapter($objects));
    }

    /**
     * @param array<string, null|string|string[]> $criteria
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = []): void
    {
        foreach ($criteria as $property => $value) {
            if (!in_array($property, array_merge($this->getClassMetadata()->getAssociationNames(), $this->getClassMetadata()->getFieldNames()), true)) {
                continue;
            }

            $name = $this->getPropertyName($property);

            if (null === $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull($name));
            } elseif (is_array($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in($name, $value));
            } elseif ('' !== $value) {
                $parameter = str_replace('.', '_', $property);
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq($name, ':' . $parameter))
                    ->setParameter($parameter, $value)
                ;
            }
        }
    }

    /**
     * @param string[] $sorting
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorting = []): void
    {
        foreach ($sorting as $property => $order) {
            if (!in_array($property, array_merge($this->getClassMetadata()->getAssociationNames(), $this->getClassMetadata()->getFieldNames()), true)) {
                continue;
            }

            if (!empty($order)) {
                $queryBuilder->addOrderBy($this->getPropertyName($property), $order);
            }
        }
    }

    protected function getPropertyName(string $name): string
    {
        if (!str_contains($name, '.')) {
            return 'o' . '.' . $name;
        }

        return $name;
    }
}
