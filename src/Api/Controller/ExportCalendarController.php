<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity as E;
use App\Entity\User;
use App\Repository\CustomEventRepository;
use App\Repository\EventRepository;
use App\Service\CalendarExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ExportCalendarController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly CustomEventRepository $customEventRepository,
        private readonly CalendarExportService $calendarExportService,
    ) {
    }

    public function __invoke(#[CurrentUser] ?User $user): Response
    {
        $events = $this->getEvents($user);
        $ical = $this->calendarExportService->exportToIcal($events);

        return new Response($ical, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="jamboree_calendar.ics"',
        ]);
    }

    /**
     * @return array<array-key, E\UserEvent|E\Contract\EventInterface>
     */
    private function getEvents(?E\User $user): array
    {
        if ($user instanceof E\User) {
            return $user->getEvents()->toArray();
        }

        return array_merge(
            $this->eventRepository
                ->getUpcomingEventsQueryBuilder('holdingDate', 'ASC')
                ->getQuery()
                ->getResult(),
            $this->customEventRepository
                ->getUpcomingEventsQueryBuilder('holdingDate', 'ASC')
                ->getQuery()
                ->getResult()
        );
    }
}
