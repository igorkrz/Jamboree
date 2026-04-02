<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserEvent;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class UserTagExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Tag::class !== $resourceClass || !isset($context['filters']['usedByMe'])) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        
        $ueSubQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(UserEvent::class, 'ue')
            ->innerJoin('ue.event', 'e')
            ->innerJoin('e.tags', 't')
            ->where('ue.user = :currentUser')
            ->andWhere(sprintf('t = %s', $rootAlias));

        $queryBuilder
            ->andWhere(sprintf('EXISTS (%s)', $ueSubQuery->getDQL()))
            ->setParameter('currentUser', $user->getObjectIdentifier());
    }
}
