<?php

declare(strict_types=1);

namespace App\Entity\Contract;

use App\Entity\EventProvider;
use App\Entity\Location;

interface EventInterface extends ResourceInterface, TaggableInterface
{
    public function getName(): ?string;

    public function setName(?string $name = null): self;

    public function getDescription(): ?string;

    public function setDescription(?string $description = null): self;

    public function getPrice(): ?string;

    public function setPrice(?string $price = null): self;

    public function getUrl(): ?string;

    public function setUrl(?string $url = null): self;

    public function getImageUrl(): ?string;

    public function setImageUrl(?string $imageUrl = null): self;

    public function getHoldingDate(): ?\DateTimeInterface;

    public function setHoldingDate(?\DateTimeInterface $holdingDate = null): self;

    public function getLocation(): ?Location;

    public function setLocation(?Location $location = null): self;
}
