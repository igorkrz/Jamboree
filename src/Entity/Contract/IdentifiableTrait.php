<?php

declare(strict_types=1);

namespace App\Entity\Contract;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

trait IdentifiableTrait
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected Ulid $id;

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getObjectIdentifier(): string
    {
        return $this->id->toRfc4122();
    }
}
