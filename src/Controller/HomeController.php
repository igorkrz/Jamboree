<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Event\CalendarEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route(path: '/', name: 'home')]
    public function indexAction(Request $request): Response
    {
        // Get current year and month
        $today = new \DateTime();
        $year = (int) $today->format('Y');
        $month = (int) $today->format('m');

        // Check for query parameters to change the month
        if ($request->query->has('year') && $request->query->has('month')) {
            $year = (int) $request->query->get('year');
            $month = (int) $request->query->get('month');
        }

        // Get the first day of the current month
        $firstDayOfMonth = new \DateTime("$year-$month-01");

        // Get the number of days in the month
        $daysInMonth = $firstDayOfMonth->format('t');

        // Get the day of the week the month starts on (0 = Monday, 6 = Sunday)
        $startDayOfWeek = (int) $firstDayOfMonth->format('w') - 1;
        if ($startDayOfWeek < 0) $startDayOfWeek = 6; // Adjust for Sunday

        // Generate the days array
        $days = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $days[] = $day;
        }

        // Calculate previous and next month
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        return $this->render('views/home.html.twig', [
            'year' => $year,
            'month' => $month,
            'days' => $days,
            'startDayOfWeek' => $startDayOfWeek,
            'prevYear' => $prevYear,
            'prevMonth' => $prevMonth,
            'nextYear' => $nextYear,
            'nextMonth' => $nextMonth,
        ]);
    }

    #[Route(path: '/export', name: 'export_calendar', methods: 'POST')]
    public function export(Request $request)
    {
        $start = new \DateTime($request->get('start', 'now'));
        $end = new \DateTime($request->get('end', 'now'));
        $filters = $request->get('filters', '{}');
        $filters = \is_array($filters) ? $filters : json_decode($filters, true);

        $event = $this->eventDispatcher->dispatch(
            new CalendarEvent(
                start: $start,
                end: $end,
                filters: $filters,
            ),
            CalendarEvents::SET_DATA
        );
        $content = $this->serializer->serialize($event->getEvents(), 'json');

        dd($content);
    }
}
