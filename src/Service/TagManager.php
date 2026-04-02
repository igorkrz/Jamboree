<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;

final readonly class TagManager
{
    public function __construct(
        private TagRepository $tagRepository,
    ) {
    }

    public function getOrCreateTag(string $name): Tag
    {
        $name = trim($name);
        $tag = $this->tagRepository->findOneBy(['name' => $name]);

        if (!$tag) {
            $tag = new Tag();
            $tag->setName($name);
            $this->tagRepository->add($tag);
        }

        return $tag;
    }
}
