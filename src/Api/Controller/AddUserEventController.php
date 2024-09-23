<?php

namespace App\Api\Controller;

use App\Entity\AccessToken;
use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEvent;
use App\Factory\UserEventFactory;
use App\Security\AccessTokenHandler;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Event\CalendarEvent;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\AccessToken\HeaderAccessTokenExtractor;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
final class AddUserEventController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(#[CurrentUser] ?User $user, UserEvent $userEvent, Request $request): JsonResponse
    {
        $user->addUserEvent($userEvent);
        $this->entityManager->flush();

        return $this->json(['user_event' => $userEvent->getId()->toRfc4122()]);
    }
}
