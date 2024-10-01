<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity()]
#[ORM\Table(name: 'event_media_object')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['event:read']],
    denormalizationContext: ['groups' => ['event:write']]
)]
class EventMediaObject extends MediaObject
{
    #[Vich\UploadableField(mapping: 'events', fileNameProperty: 'fileName')]
    protected ?File $file = null;
}
