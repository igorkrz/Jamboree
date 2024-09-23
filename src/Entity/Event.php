<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Api\Controller\CalendarController;
use App\Api\Controller\RemoveUserEventController;
use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new GetCollection(
            uriTemplate: 'calendar',
            controller: CalendarController::class,
        ),
        new Get(),
        new Delete(
            uriTemplate: '/events/{id}/remove',
            controller: RemoveUserEventController::class,
            denormalizationContext: [
                'groups' => ['user_event_write']
            ],
        )
    ],
    normalizationContext: [
        'groups' => ['event_read']
    ],
)]
class Event extends AbstractEvent
{
    #[ORM\ManyToOne(targetEntity: EventProvider::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'provider_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['event_read'])]
    protected EventProvider $provider;

    public function getProvider(): EventProvider
    {
        return $this->provider;
    }

    public function setProvider(EventProvider $provider = null): static
    {
        $this->provider = $provider;

        return $this;
    }
}
