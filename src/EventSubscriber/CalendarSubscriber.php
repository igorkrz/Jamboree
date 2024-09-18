<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity as E;
use App\Repository\EventRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly EventRepository $eventRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendarEvent): void
    {
        $events = $this->getEvents();

        foreach ($events as $event) {
            $this->createCalendarEvent($calendarEvent, $event);
        }
    }

    /**
     * @return E\Event[]|PersistentCollection<E\UserEvent>
     */
    private function getEvents(): array|PersistentCollection
    {
        $user = $this->security->getUser();

        if ($user instanceof E\User) {
            return $user->getEvents();
        }

        return $this->eventRepository
            ->getUpcomingEventsQueryBuilder('holdingDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function createCalendarEvent(CalendarEvent $calendarEvent, E\Event|E\UserEvent $event): void
    {
        if ($event instanceof E\UserEvent) {
            $calendarEvent->addEvent(new Event(
                title: $event->getEvent()->getName(),
                start: $event->getEvent()->getHoldingDate(),
                options: [
                    'url' => $this->urlGenerator->generate(
                        name: 'event',
                        parameters: [
                            'id' => $event->getEvent()->getId()
                        ]),
                ]
            ));
            return;
        }

        $calendarEvent->addEvent(new Event(
            title: $event->getName(),
            start: $event->getHoldingDate(),
            options: [
                'url' => $this->urlGenerator->generate(
                    name: 'event',
                    parameters: [
                        'id' => $event->getId()
                    ]),
            ]
        ));
    }
}