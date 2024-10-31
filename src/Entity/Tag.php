<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contract\EventInterface;
use App\Entity\Contract\IdentifiableTrait;
use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tag')]
class Tag implements TimestampableInterface, ResourceInterface
{
    use TimestampableTrait;
    use IdentifiableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[Groups(['event:read', 'user_event:write'])]
    protected Ulid $id;

    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(['event:read', 'custom_event:write'])]
    protected string $name;

    /**
     * @var Collection<int, EventInterface>
     */
    #[ORM\ManyToMany(targetEntity: AbstractEvent::class, mappedBy: 'tags')]
    protected Collection $events;

    public function __construct(Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
        $this->events = new ArrayCollection();
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

    /**
     * @return Collection<int, EventInterface>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }
}
