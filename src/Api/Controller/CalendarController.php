<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity as E;
use App\Entity\User;
use App\Repository\EventRepository;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\SetDataEvent;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use function array_map;
use function is_string;
use function is_array;
use function json_decode;

final class CalendarController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {
    }

    public function __invoke(#[CurrentUser] ?User $user, Request $request): array
    {
        $start = is_string($request->query->get('start')) ? $request->query->get('start') : '';
        $end = is_string($request->query->get('end')) ? $request->query->get('end') : '';

        try {
            $start = new DateTime($start);
            $end = new DateTime($end === '' ? '+5years' : $end);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 422);
        }

        $filters = $request->get('filters', '{}');
        $filters = is_array($filters) ? $filters : json_decode($filters, true);

        $events = $this->getEvents($user);
        $calendarEvent = new SetDataEvent($start, $end, $filters);

        foreach ($events as $event) {
            $this->createCalendarEvent($calendarEvent, $event);
        }

        return array_map(fn (Event $event) => $event->toArray(), $calendarEvent->getEvents());
    }

    /**
     * @return Collection<int|string, E\UserEvent>|E\Event[]
     */
    private function getEvents(?E\User $user): Collection|array
    {
        if ($user instanceof E\User) {
            return $user->getEvents();
        }

        return $this->eventRepository
            ->getUpcomingEventsQueryBuilder('holdingDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function createCalendarEvent(SetDataEvent $calendarEvent, E\Event|E\UserEvent $event): void
    {
        if ($event instanceof E\UserEvent) {
            $calendarEvent->addEvent(new Event(
                title: $event->getEvent()->getName(),
                start: $event->getEvent()->getHoldingDate(),
                resourceId: $event->getEvent()->getObjectIdentifier(),
                options: ['url' => $this->resolveEventUrl($event)]
            ));

            return;
        }

        $calendarEvent->addEvent(new Event(
            title: $event->getName(),
            start: $event->getHoldingDate(),
            resourceId: $event->getObjectIdentifier(),
            options: ['url' => $this->resolveEventUrl($event)]
        ));
    }

    private function resolveEventUrl(E\Event|E\UserEvent $event): string
    {
        if ($event instanceof E\Event) {
            return '/events/' . $event->getId() . '/';
        }

        if ($event->getEvent() instanceof E\CustomEvent) {
            return '/custom_events/' . $event->getEvent()->getId() . '/';
        }

        return '/events/' . $event->getEvent()->getId() . '/';
    }
}
