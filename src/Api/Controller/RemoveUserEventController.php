<?php

namespace App\Api\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class RemoveUserEventController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(#[CurrentUser] ?User $user, Event $event, Request $request): JsonResponse
    {
        $userEvents = $user->getEvents();

        /** @var UserEvent $userEvent */
        foreach ($userEvents as $userEvent) {
            if ($userEvent->getEvent()->getId()->toRfc4122() === $event->getId()->toRfc4122()) {
                $user->removeUserEvent($userEvent);
                $this->entityManager->flush();

                return $this->json(['user_event' => $userEvent->getId()->toRfc4122()]);
            }
        }

        return $this->json('fail', 422);
    }
}
