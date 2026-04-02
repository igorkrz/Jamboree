<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Api\Controller\CreateCustomEventController;
use App\Repository\CustomEventRepository;
use ArrayObject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: CustomEventRepository::class)]
#[ORM\Table(name: 'custom_event')]
#[ApiResource(
    operations: [
        new GetCollection(security: 'is_granted("ROLE_USER")'),
        new Get(security: 'is_granted("ROLE_USER") and object.getUser() === user'),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            controller: CreateCustomEventController::class,
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                    ],
                                    'url' => [
                                        'type' => 'string',
                                        'nullable' => true,
                                    ],
                                    'description' => [
                                        'type' => 'string',
                                        'nullable' => true,
                                    ],
                                    'price' => [
                                        'type' => 'integer',
                                        'nullable' => true,
                                    ],
                                    'holdingDate' => [
                                        'type' => 'string',
                                    ],
                                    'picture' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                        'nullable' => true,
                                    ],
                                    'location' => [
                                        'type' => 'object',
                                        'nullable' => true,
                                        'properties' => [
                                            'venue' => [
                                                'type' => 'string',
                                                'nullable' => true,
                                            ],
                                            'city' => [
                                                'type' => 'string',
                                                'nullable' => true,
                                            ],
                                        ],
                                    ],
                                ]
                            ]
                        ]
                    ])
                )
            )
        )
    ],
    normalizationContext: ['groups' => ['custom_event:read', 'event:read']],
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
