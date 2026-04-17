<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
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
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['tag:read']],
    order: ['name' => 'ASC'],
)]
class Tag implements TimestampableInterface, ResourceInterface
{
    use TimestampableTrait;
    use IdentifiableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[Groups(['event:read', 'user_event:write', 'tag:read', 'artist:read'])]
    protected Ulid $id;

    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(['event:read', 'custom_event:write', 'tag:read', 'artist:read'])]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'description' => 'Filter tags that are used by current user events',
            'name' => 'usedByMe'
        ]
    )]
    protected string $name;

    /**
     * @var Collection<array-key, Artist>
     */
    #[ORM\ManyToMany(targetEntity: Artist::class, mappedBy: 'tags')]
    private Collection $artists;

    /**
     * @var Collection<array-key, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'tags')]
    #[Groups(['tag:read'])]
    protected Collection $events;

    /**
     * @var Collection<array-key, CustomEvent>
     */
    #[ORM\ManyToMany(targetEntity: CustomEvent::class, mappedBy: 'tags')]
    #[Groups(['tag:read'])]
    protected Collection $customEvents;

    public function __construct(?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
        $this->events = new ArrayCollection();
        $this->customEvents = new ArrayCollection();
        $this->artists = new ArrayCollection();
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
     * @return Collection<array-key, Artist>
     */
    public function getArtists(): Collection
    {
        return $this->artists;
    }

    public function addArtist(Artist $artist): static
    {
        if (!$this->hasArtist($artist)) {
            $this->artists->add($artist);
        }

        return $this;
    }

    public function hasArtist(Artist $artist): bool
    {
        foreach ($this->artists as $existingArtist) {
            if ($existingArtist->getObjectIdentifier() === $artist->getObjectIdentifier()) {
                return true;
            }
        }

        return false;
    }

    public function removeArtist(Artist $artist): static
    {
        if ($this->hasArtist($artist)) {
            $this->artists->removeElement($artist);
        }

        return $this;
    }

    /**
     * @return Collection<array-key, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->hasEvent($event)) {
            $this->events->add($event);
        }

        return $this;
    }

    public function hasEvent(Event $event): bool
    {
        foreach ($this->events as $existingEvent) {
            if ($existingEvent->getObjectIdentifier() === $event->getObjectIdentifier()) {
                return true;
            }
        }

        return false;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->hasEvent($event)) {
            $this->events->removeElement($event);
        }

        return $this;
    }

    /**
     * @return Collection<array-key, CustomEvent>
     */
    public function getCustomEvents(): Collection
    {
        return $this->customEvents;
    }

    public function addCustomEvent(CustomEvent $customEvent): static
    {
        if (!$this->hasCustomEvent($customEvent)) {
            $this->customEvents->add($customEvent);
        }

        return $this;
    }

    public function hasCustomEvent(CustomEvent $customEvent): bool
    {
        foreach ($this->customEvents as $existingCustomEvent) {
            if ($existingCustomEvent->getObjectIdentifier() === $customEvent->getObjectIdentifier()) {
                return true;
            }
        }

        return false;
    }

    public function removeCustomEvent(CustomEvent $customEvent): static
    {
        if ($this->hasCustomEvent($customEvent)) {
            $this->customEvents->removeElement($customEvent);
        }

        return $this;
    }
}
