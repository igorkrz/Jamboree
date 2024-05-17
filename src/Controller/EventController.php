<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {
    }

    #[Route(path: '/event/{id}', name: 'event')]
    public function getResource(string $id): Response
    {
        $event = $this->eventRepository->find($id);

        return $this->render('views/single_event.html.twig', ['event' => $event]);
    }
}
