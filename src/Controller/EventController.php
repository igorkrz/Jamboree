<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contract\EventInterface;
use App\Repository\CustomEventRepository;
use App\Repository\EventRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly CustomEventRepository $customEventRepository,
    ) {
    }

    #[Route(path: '/events', name: 'events')]
    public function getList(Request $request): Response
    {
        $sortField = (string) $request->query->get('sort', 'holdingDate');
        $sortOrder = (string) $request->query->get('order', 'ASC');
        $page = (int) $request->query->get('page', 1);
        $viewType = (string) $request->query->get('view', 'grid');

        $events = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($this->eventRepository->getUpcomingEventsQueryBuilder($sortField, $sortOrder)),
            $page,
            10,
        );

        return $this->render('views/list_event.html.twig', [
            'events' => $events,
            'view' => $viewType,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'page' => $page,
        ]);
    }

    #[Route(path: '/events/{id}', name: 'event', methods: ['GET'])]
    public function getResource(string $id): Response
    {
        $event = $this->eventRepository->find($id);

        if (!$event instanceof EventInterface) {
            $event = $this->customEventRepository->find($id);
        }

        return $this->render('views/single_event.html.twig', ['event' => $event]);
    }
}
