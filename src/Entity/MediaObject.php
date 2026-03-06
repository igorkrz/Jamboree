<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contract\IdentifiableTrait;
use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
abstract class MediaObject implements ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;
    use IdentifiableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid')]
    #[Groups(['event:read', 'custom_event:read'])]
    protected Ulid $id;

    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'fileName')]
    protected ?File $file = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['event:read', 'custom_event:read', 'custom_event:write'])]
    protected ?string $fileName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['event:read', 'custom_event:read', 'custom_event:write'])]
    protected ?string $filePath = null;

    public function __construct(?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null): static
    {
        if ($this->file !== $file) {
            $this->updatedAt = new \DateTime();

            $this->file = $file;
        }

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName = null): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath = null): self
    {
        $this->filePath = $filePath;

        return $this;
    }
}
