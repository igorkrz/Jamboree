<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity as E;
use App\Entity\User;
use App\Repository\CustomEventRepository;
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
        private readonly CustomEventRepository $customEventRepository,
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

        return array_map(function (Event $event) use ($user) {
            $data = $event->toArray();
            if ($user instanceof User) {
                $data['google_url'] = $this->generateGoogleCalendarUrl($event);
            }

            return $data;
        }, $calendarEvent->getEvents());
    }

    private function generateGoogleCalendarUrl(Event $event): string
    {
        $start = $event->getStart()->format('Ymd');
        $end = (clone $event->getStart())->modify('+1 day')->format('Ymd');

        $params = [
            'action' => 'TEMPLATE',
            'text' => $event->getTitle(),
            'dates' => "$start/$end",
        ];

        if (isset($event->getOptions()['description'])) {
            $params['details'] = $event->getOptions()['description'];
        }

        if (isset($event->getOptions()['location'])) {
            $params['location'] = $event->getOptions()['location'];
        }

        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }

    /**
     * @return array<array-key, E\UserEvent>|E\Contract\EventInterface>
     */
    private function getEvents(?E\User $user): Collection|array
    {
        if ($user instanceof E\User) {
            return $user->getEvents();
        }

        return array_merge(
            $this->eventRepository
                ->getUpcomingEventsQueryBuilder('holdingDate', 'ASC')
                ->getQuery()
                ->getResult(),
            $this->customEventRepository
                ->getUpcomingEventsQueryBuilder('holdingDate', 'ASC')
                ->getQuery()
                ->getResult(),
        );
    }

    private function createCalendarEvent(SetDataEvent $calendarEvent, E\Contract\EventInterface|E\UserEvent $event): void
    {
        $eventData = $event instanceof E\UserEvent ? $event->getEvent() : $event;
        $options = [
            'url' => $this->resolveEventUrl($event),
            'description' => $eventData->getDescription(),
        ];

        $location = $eventData->getLocation();
        if ($location instanceof E\Location) {
            $parts = array_filter([
                $location->getVenue(),
                $location->getAddressLine(),
                $location->getCity(),
                $location->getCountry(),
            ]);
            $options['location'] = implode(', ', $parts);
        }

        $calendarEvent->addEvent(new Event(
            title: $eventData->getName(),
            start: $eventData->getHoldingDate(),
            resourceId: $eventData->getObjectIdentifier(),
            options: $options,
        ));
    }

    private function resolveEventUrl(E\Contract\EventInterface|E\UserEvent $event): string
    {
        if ($event instanceof E\CustomEvent) {
            return '/custom_events/' . $event->getId() . '/';
        }

        if ($event instanceof E\Event) {
            return '/events/' . $event->getId() . '/';
        }

        if ($event->getEvent() instanceof E\CustomEvent) {
            return '/custom_events/' . $event->getEvent()->getId() . '/';
        }

        return '/events/' . $event->getEvent()->getId() . '/';
    }
}
