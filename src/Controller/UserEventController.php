<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contract\EventInterface;
use App\Entity\User;
use App\Entity\UserEvent;
use App\Factory\UserEventFactory;
use App\Repository\CustomEventRepository;
use App\Repository\EventRepository;
use App\Repository\UserEventRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserEventController extends AbstractController
{
    public function __construct(
        private readonly UserEventFactory $userEventFactory,
        private readonly UserEventRepository $userEventRepository,
        private readonly EventRepository $eventRepository,
        private readonly CustomEventRepository $customEventRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    #[Route(path: '/user/events', name: 'user_events')]
    public function getList(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirect('/login');
        }

        $sortField = (string) $request->query->get('sort', 'holdingDate');
        $sortOrder = (string) $request->query->get('order', Order::Ascending->value);
        $page = (int) $request->query->get('page', 1);
        $viewType = (string) $request->query->get('view', 'grid');

        $userEvents = $this->userEventRepository->getUpcomingEvents($user);

        $this->sortEvents($userEvents, $sortField, $sortOrder);

        $userEvents = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new ArrayAdapter($userEvents),
            $page,
            10,
        );

        return $this->render('views/list_user_event.html.twig', [
            'userEvents' => $userEvents,
            'view' => $viewType,
        ]);
    }

    #[Route(path: '/user/events/{id}', name: 'user_event_add', methods: ['POST'])]
    public function add(Request $request, string $id): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirect('/login');
        }

        $event = $this->getEvent($id);

        $userEvent = $this->userEventFactory->create();
        $userEvent->setEvent($event);

        $user->addUserEvent($userEvent);
        $this->entityManager->flush();

        $this->addFlash('success', 'Event has been added to your collection.');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/user_events/{id}', name: 'user_event_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, string $id): Response
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

    private function getEvent(string $id): EventInterface
    {
        $event = $this->eventRepository->find($id);

        if ($event instanceof EventInterface) {
            return $event;
        }

        $event = $this->customEventRepository->find($id);

        if (!$event instanceof EventInterface) {
            throw new InvalidArgumentException();
        }

        return $event;
    }

    /**
     * @param UserEvent[] $userEvents
     */
    private function sortEvents(array &$userEvents, string $sortField, string $sortOrder): void
    {
        usort($userEvents, function ($a, $b) use ($sortField, $sortOrder) {
            $aField = !$a instanceof UserEvent ?: $this->getPropertyValue($a, $sortField);
            $bField = !$b instanceof UserEvent ?: $this->getPropertyValue($b, $sortField);

            if ($sortOrder === Order::Ascending->value) {
                return $aField <=> $bField;
            }
            return $bField <=> $aField;
        });
    }

    private function getPropertyValue(UserEvent $userEvent, string $sortField): DateTimeInterface|string
    {
        return $this->propertyAccessor->getValue($userEvent->getEvent(), $sortField);
    }
}
