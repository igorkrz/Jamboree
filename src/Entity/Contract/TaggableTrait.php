<?php

declare(strict_types=1);

namespace App\Entity\Contract;

use App\Entity\Tag;
use Doctrine\Common\Collections\Collection;

trait TaggableTrait
{
    /**
     * @var Collection<int, Tag>
     */
    protected Collection $tags;

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->hasTag($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function hasTag(Tag $eventTag): bool
    {
        foreach ($this->getTags() as $tag) {
            if ($eventTag->getObjectIdentifier() === $tag->getObjectIdentifier()) {
                return true;
            }
        }

        return false;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->hasTag($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }
}
