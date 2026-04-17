<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ArtistMediaObjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ArtistMediaObjectRepository::class)]
#[ORM\Table(name: 'artist_media_object')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['artist:read']],
    denormalizationContext: ['groups' => ['artist:write']]
)]
class ArtistMediaObject extends MediaObject
{
    #[Vich\UploadableField(mapping: 'artists', fileNameProperty: 'fileName')]
    protected ?File $file = null;
}
