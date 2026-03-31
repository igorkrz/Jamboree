<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CustomEventMediaObjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: CustomEventMediaObjectRepository::class)]
#[ORM\Table(name: 'custom_event_media_object')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['custom_event:read']],
    denormalizationContext: ['groups' => ['custom_event:write']]
)]
class CustomEventMediaObject extends MediaObject
{
    #[Vich\UploadableField(mapping: 'custom_events', fileNameProperty: 'fileName')]
    #[Groups(['custom_event:write'])]
    protected ?File $file = null;
}
