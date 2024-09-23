<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Contract\ResourceInterface;
use App\Repository\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
#[ORM\Table(name: 'location')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ]
)]
class Location implements ResourceInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private Ulid $id;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['event_read'])]
    private ?string $venue = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['event_read'])]
    private ?string $city = null;

    public function __construct(Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getVenue(): ?string
    {
        return $this->venue;
    }

    public function setVenue(?string $venue = null): static
    {
        $this->venue = $venue;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }
}
