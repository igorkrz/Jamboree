<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Api\Controller\CreateCustomEventController;
use App\Api\Controller\RemoveUserEventController;
use App\Repository\CustomEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: CustomEventRepository::class)]
#[ORM\Table(name: 'custom_event')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            controller: CreateCustomEventController::class,
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            )
        )
    ],
    normalizationContext: ['groups' => ['event:read']],
    denormalizationContext: ['groups' => ['custom_event:write']],
)]
class CustomEvent extends AbstractEvent
{
    #[ORM\OneToOne(targetEntity: CustomEventMediaObject::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['custom_event:read', 'custom_event:write'])]
    protected ?CustomEventMediaObject $picture = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    protected User $user;

    public function getPicture(): ?CustomEventMediaObject
    {
        return $this->picture;
    }

    public function setPicture(?CustomEventMediaObject $picture = null): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
