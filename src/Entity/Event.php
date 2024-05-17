<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event implements ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected Ulid $id;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $internalCode = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $location = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $price = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Url]
    protected ?string $url = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Url]
    protected ?string $imageUrl = null;

    #[ORM\Column(type: 'datetimetz', nullable: true)]
    protected ?\DateTimeInterface $holdingDate = null;

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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location = null): static
    {
        $this->location = $location;

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
}
