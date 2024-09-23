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

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
#[ApiResource(
    operations: [
        new GetCollection(
            //            paginationEnabled: true,
        ),
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
    ]
)]
class Event extends AbstractEvent
{
}
