<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Form\SortType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {
    }

    #[Route(path: '/events', name: 'events')]
    public function getList(Request $request): Response
    {
        $events = $this->eventRepository->findAll();

        $form = $this->createForm(SortType::class, $events, []);

        $sort = $request->get('sort');

        if (isset($sort['sort'])) {
            $events = $this->sortEvents($sort['sort'], $events);
        }

        return $this->render('views/list_event.html.twig', [
            'events' => $events,
            'form' => $form,
        ]);
    }

    #[Route(path: '/event/{id}', name: 'event')]
    public function getResource(string $id): Response
    {
        $event = $this->eventRepository->find($id);

        return $this->render('views/single_event.html.twig', ['event' => $event]);
    }

    /**
     * @param Event[] $events
     * @return Event[]
     */
    private function sortEvents(string $by, array &$events): array
    {
        usort($events, function (Event $event1, Event $event2) use ($by): int {
            return $event1->$by() > $event2->$by() ? 1 : -1;
        });

        return $events;
    }
}
