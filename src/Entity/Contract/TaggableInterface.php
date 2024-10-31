<?php

declare(strict_types=1);

namespace App\Entity\Contract;

use App\Entity\Tag;
use Doctrine\Common\Collections\Collection;

interface TaggableInterface
{
    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection;

    public function hasTag(Tag $eventTag): bool;

    public function addTag(Tag $tag): self;

    public function removeTag(Tag $tag): self;
}
