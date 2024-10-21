<?php

declare(strict_types=1);

namespace App\Api\Controller;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Contract\EventInterface;
use App\Entity\CustomEvent;
use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
final class AddUserEventController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function __invoke(#[CurrentUser] ?User $user, UserEvent $userEvent, Request $request): JsonResponse
    {
        $event = $this->getEvent($request);

        if (!$event instanceof EventInterface) {
            return $this->json('Wrong argument', 422);
        }

        $userEvent->setEvent($event);
        $user->addUserEvent($userEvent);
        $this->entityManager->flush();

        return $this->json(['user_event' => $userEvent->getId()->toRfc4122()]);
    }

    private function getEvent(Request $request): ?EventInterface
    {
        $content = json_decode($request->getContent(), true);

        if (isset($content['event'])) {
            /** @var Event $event */
            $event = $this->iriConverter->getResourceFromIri($content['event']);
            return $event;
        }

        if (isset($content['custom_event'])) {
            /** @var CustomEvent $customEvent */
            $customEvent = $this->iriConverter->getResourceFromIri($content['custom_event']);
            return $customEvent;
        }

        return null;
    }
}
