<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;
use App\Entity\Contract\ResourceInterface;

/**
 * @template T of object
 *
 * @extends ObjectRepository<T>
 */
interface RepositoryInterface extends ObjectRepository
{
    public const ORDER_ASCENDING = 'ASC';

    public const ORDER_DESCENDING = 'DESC';

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string> $sorting
     *
     * @return iterable<T>
     */
    public function createPaginator(array $criteria = [], array $sorting = []): iterable;

    public function add(ResourceInterface $resource): void;

    public function remove(ResourceInterface $resource): void;
}
