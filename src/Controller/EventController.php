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
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
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

        $userEvents = $user->getEvents()->toArray();

        $events = array_map(fn(UserEvent $userEvent) => $userEvent->getEvent(), $userEvents);

        $form = $this->createForm(SortType::class, $events, []);

        $sort = $request->get('sort');

        if (isset($sort['sort'])) {
            $events = $this->sortEvents($sort['sort'], $events);
        }

        return $this->render('views/list_user_event.html.twig', [
            'userEvents' => $user->getEvents(),
            'form' => $form,
        ]);
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
