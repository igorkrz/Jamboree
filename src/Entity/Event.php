<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Api\Controller\CalendarController;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
#[ApiFilter(DateFilter::class, properties: ['holdingDate'])]
#[ApiResource(
    operations: [
        new GetCollection(
            filters: [DateFilter::class]
        ),
        new GetCollection(
            uriTemplate: 'calendar',
            controller: CalendarController::class,
        ),
        new Get(),
    ],
    normalizationContext: ['groups' => ['event:read']],
    denormalizationContext: ['groups' => ['event:write']],
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
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'events', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'event_tag')]
    protected Collection $tags;

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

    public function setProvider(EventProvider $provider = null): static
    {
        $this->provider = $provider;

        return $this;
    }
}
