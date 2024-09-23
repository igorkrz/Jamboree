<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Event\CalendarEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'home')]
    public function indexAction(Request $request): Response
    {
        return $this->render('views/base.html.twig');
    }
}
