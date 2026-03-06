<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Contract\IdentifiableTrait;
use App\Entity\Contract\ResourceInterface;
use App\Repository\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
#[ORM\Table(name: 'event_provider')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ]
)]
class EventProvider implements ResourceInterface
{
    use IdentifiableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected Ulid $id;

    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(['event:read'])]
    private string $name;

    public function __construct(?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
