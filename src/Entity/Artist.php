<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Api\Controller\GetArtistInfoController;
use App\Entity\Contract\IdentifiableTrait;
use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TaggableInterface;
use App\Entity\Contract\TaggableTrait;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
#[ORM\Table(name: 'artist')]
#[ApiResource(
    operations: [
        new Get(controller: GetArtistInfoController::class),
    ],
    normalizationContext: ['groups' => ['artist:read', 'event:read']],
)]
class Artist implements ResourceInterface, TimestampableInterface, TaggableInterface
{
    use IdentifiableTrait;
    use TimestampableTrait;
    use TaggableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[Groups(['event:read', 'artist:read'])]
    protected Ulid $id;

    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(['event:read', 'artist:read'])]
    protected string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['event:read', 'artist:read'])]
    protected ?string $summary = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['event:read', 'artist:read'])]
    protected ?string $bio = null;

    #[ORM\OneToOne(targetEntity: ArtistMediaObject::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['event:read', 'artist:read'])]
    protected ?ArtistMediaObject $picture = null;

    /**
     * @var Collection<array-key, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'artists')]
    private Collection $events;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'artists', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'artist_tag')]
    #[Groups(['artist:read'])]
    protected Collection $tags;

    public function __construct(?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
        $this->events = new ArrayCollection();
        $this->tags = new ArrayCollection();
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

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getPicture(): ?ArtistMediaObject
    {
        return $this->picture;
    }

    public function setPicture(?ArtistMediaObject $picture = null): static
    {
        $this->picture = $picture;

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
}
