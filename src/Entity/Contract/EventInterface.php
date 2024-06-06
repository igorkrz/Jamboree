<?php

declare(strict_types=1);

namespace App\Entity\Contract;

interface EventInterface extends ResourceInterface
{
    public function getInternalCode(): ?string;

    public function setInternalCode(?string $internalCode = null): self;

    public function getName(): ?string;

    public function setName(?string $name = null): self;

    public function getDescription(): ?string;

    public function setDescription(?string $description = null): self;

    public function getLocation(): ?string;

    public function setLocation(?string $location = null): self;

    public function getPrice(): ?string;

    public function setPrice(?string $price = null): self;

    public function getUrl(): ?string;

    public function setUrl(?string $url = null): self;

    public function getImageUrl(): ?string;

    public function setImageUrl(?string $imageUrl = null): self;

    public function getHoldingDate(): ?\DateTimeInterface;

    public function setHoldingDate(?\DateTimeInterface $holdingDate = null): self;
}
