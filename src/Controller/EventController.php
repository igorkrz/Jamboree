<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEvent;
use App\Factory\UserEventFactory;
use App\Form\SortType;
use App\Repository\EventRepository;
use App\Repository\UserEventRepository;
use ArrayIterator;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class EventController extends AbstractController
{
    public function __construct(
        private readonly UserEventFactory $userEventFactory,
        private readonly UserEventRepository $userEventRepository,
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/events', name: 'events')]
    public function getList(Request $request): Response
    {
        $sortField = (string) $request->query->get('sort', 'holdingDate');
        $sortOrder = (string) $request->query->get('order', 'ASC');
        $page = (int) $request->query->get('page', 1);

        $events = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($this->eventRepository->getUpcomingEventsQueryBuilder($sortField, $sortOrder)),
            $page,
            10,
        );

        return $this->render('views/list_event.html.twig', ['events' => $events]);
    }

    #[Route(path: '/events/{id}', name: 'event', methods: ['GET'])]
    public function getResource(string $id): Response
    {
        $event = $this->eventRepository->find($id);

        return $this->render('views/single_event.html.twig', ['event' => $event]);
    }

    #[Route(path: '/user/events/{id}', name: 'user_event_add', methods: ['POST'])]
    public function addUserEvent(Request $request, string $id): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirect('/login');
        }

        $event = $this->eventRepository->find($id);

        if (!$event instanceof Event) {
            throw new InvalidArgumentException();
        }

        $userEvent = $this->userEventFactory->create();
        $userEvent->setEvent($event);

        $user->addUserEvent($userEvent);
        $this->entityManager->flush();

        $this->addFlash('success', 'Event has been added to your collection.');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/user_events/{id}', name: 'user_event_delete', methods: ['POST', 'DELETE'])]
    public function deleteUserEvent(Request $request, string $id): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirect('/login');
        }

        $userEvent = $this->userEventRepository->find($id);

        if (!$userEvent instanceof UserEvent) {
            throw new InvalidArgumentException();
        }

        $user->removeUserEvent($userEvent);
        $this->entityManager->flush();

        $this->addFlash('success', 'Event has been removed from your collection.');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/user/events', name: 'user_events')]
    public function getUserEvents(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirect('/login');
        }

        $sortField = (string) $request->query->get('sort', 'holdingDate');
        $sortOrder = (string) $request->query->get('order', 'ASC');
        $page = (int) $request->query->get('page', 1);

        $userEvents = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($this->userEventRepository->getUpcomingEventsQueryBuilder($sortField, $sortOrder)),
            $page,
            10,
        );

        return $this->render('views/list_user_event.html.twig', ['userEvents' => $userEvents]);
    }
}
