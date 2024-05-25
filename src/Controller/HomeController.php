<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {
    }

    #[Route(path: '/', name: 'home')]
    public function indexAction(): Response
    {
        $events = $this->eventRepository->findAll();

        return $this->render('views/home.html.twig', ['events' => $events]);
    }
}
