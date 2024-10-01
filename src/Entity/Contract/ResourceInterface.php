<?php

declare(strict_types=1);

namespace App\Entity\Contract;

use Symfony\Component\Uid\Ulid;

interface ResourceInterface
{
    public function getId(): Ulid;

    public function getObjectIdentifier(): string;
}
