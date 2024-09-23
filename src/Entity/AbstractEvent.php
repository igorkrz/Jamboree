<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contract\EventInterface;
use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
abstract class AbstractEvent implements TimestampableInterface, EventInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[Groups(['event_read', 'user_event_write'])]
    protected Ulid $id;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $internalCode = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['event_read'])]
    protected ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['event_read'])]
    protected ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['event_read'])]
    protected ?string $price = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Url]
    #[Groups(['event_read'])]
    protected ?string $url = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Url]
    #[Groups(['event_read'])]
    protected ?string $imageUrl = null;

    #[ORM\Column(type: 'datetimetz', nullable: true)]
    #[Groups(['event_read'])]
    protected ?\DateTimeInterface $holdingDate = null;

    #[ORM\ManyToOne(targetEntity: Location::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    #[Groups(['event_read'])]
    protected ?Location $location = null;

    public function __construct(Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
    }

    public function getId(): Ulid
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name = null): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description = null): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price = null): static
    {
        $this->price = $price;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url = null): static
    {
        $this->url = $url;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl = null): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getHoldingDate(): ?\DateTimeInterface
    {
        return $this->holdingDate;
    }

    public function setHoldingDate(?\DateTimeInterface $holdingDate = null): static
    {
        $this->holdingDate = $holdingDate;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location = null): static
    {
        $this->location = $location;

        return $this;
    }
}
