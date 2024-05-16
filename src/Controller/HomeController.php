<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {
    }

    #[Route(path: '/', name: 'home')]
    public function indexAction(): Response
    {
        $events = $this->eventRepository->findAll();

        return $this->render('home.html.twig', ['events' => $events]);
    }
}
