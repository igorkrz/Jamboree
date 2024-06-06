<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contract\EventInterface;
use App\Entity\User;
use App\Factory\CustomEventFactory;
use App\Factory\UserEventFactory;
use App\Form\CustomEventType;
use App\Repository\CustomEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CustomEventController extends AbstractController
{
    public function __construct(
        private readonly CustomEventFactory $customEventFactory,
        private readonly UserEventFactory $userEventFactory,
        private readonly CustomEventRepository $customEventRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/custom_events', name: 'custom_events')]
    public function getList(Request $request): Response
    {
        $sortField = (string) $request->query->get('sort', 'holdingDate');
        $sortOrder = (string) $request->query->get('order', 'ASC');
        $page = (int) $request->query->get('page', 1);
        $viewType = (string) $request->query->get('view', 'grid');

        $events = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($this->customEventRepository->getUpcomingEventsQueryBuilder($sortField, $sortOrder)),
            $page,
            10,
        );

        return $this->render('views/list_custom_event.html.twig', [
            'events' => $events,
            'view' => $viewType,
        ]);
    }

    #[Route(path: '/custom_events/{id}', name: 'custom_event', methods: ['GET'])]
    public function getResource(string $id): Response
    {
        $event = $this->customEventRepository->find($id);

        return $this->render('views/single_event.html.twig', ['event' => $event]);
    }

    #[Route(path: '/custom_event_create', name: 'custom_event_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirect('/login');
        }

        $customEvent = $this->customEventFactory->create();

        $form = $this->createForm(CustomEventType::class, $customEvent);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customEvent->setUser($user);

            $userEvent = $this->userEventFactory->create();
            $userEvent->setEvent($customEvent);

            $user->addUserEvent($userEvent);
            $this->entityManager->persist($customEvent);
            $this->entityManager->flush();

            $this->addFlash('success', 'Event has been added to your collection.');

            return $this->redirect('/custom_events');
        }

        return $this->render('views/create_custom_event.html.twig', ['form' => $form]);
    }

    #[Route(path: '/custom_events/{id}', name: 'custom_event_delete', methods: ['POST', 'DELETE'])]
    public function deleteResource(Request $request, string $id): Response
    {
        $customEvent = $this->customEventRepository->find($id);

        if (!$customEvent instanceof EventInterface) {
            throw new InvalidArgumentException();
        }

        $this->entityManager->remove($customEvent);
        $this->entityManager->flush();

        $this->addFlash('success', 'Custom event has been deleted.');

        return $this->redirect('/custom_events');
    }
}
