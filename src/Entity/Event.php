<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
#[ApiFilter(DateFilter::class, properties: ['holdingDate'])]
#[ApiFilter(SearchFilter::class, properties: ['provider.name' => 'exact', 'tags.name' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['holdingDate', 'name'])]
#[ApiResource(
    operations: [
        new GetCollection(filters: [DateFilter::class, SearchFilter::class, OrderFilter::class]),
        new Get(),
    ],
    normalizationContext: ['groups' => ['event:read']],
    denormalizationContext: ['groups' => ['event:write']],
    order: ['holdingDate' => 'ASC'],
)]
class Event extends AbstractEvent
{
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $internalCode = null;

    #[ORM\OneToOne(targetEntity: EventMediaObject::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups(['event:read'])]
    protected ?EventMediaObject $picture = null;

    #[ORM\ManyToOne(targetEntity: EventProvider::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'provider_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['event:read'])]
    protected EventProvider $provider;

    /**
     * @var Collection<array-key, Artist>
     */
    #[ORM\ManyToMany(targetEntity: Artist::class, inversedBy: 'events')]
    #[ORM\JoinTable(name: 'event_artist')]
    #[Groups(['event:read'])]
    private Collection $artists;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isAiEnriched = false;

    public function __construct(?Ulid $id = null)
    {
        $this->artists = new ArrayCollection();
        parent::__construct($id);
    }

    public function isAiEnriched(): bool
    {
        return $this->isAiEnriched;
    }

    public function setIsAiEnriched(bool $isAiEnriched): static
    {
        $this->isAiEnriched = $isAiEnriched;

        return $this;
    }

    public function getInternalCode(): ?string
    {
        return $this->internalCode;
    }

    public function setInternalCode(?string $internalCode = null): static
    {
        $this->internalCode = $internalCode;

        return $this;
    }

    public function getPicture(): ?EventMediaObject
    {
        return $this->picture;
    }

    public function setPicture(?EventMediaObject $picture = null): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getProvider(): EventProvider
    {
        return $this->provider;
    }

    public function setProvider(?EventProvider $provider = null): static
    {
        $this->provider = $provider;

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
}
