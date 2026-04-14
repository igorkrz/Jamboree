<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class DeleteAllNotificationsController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly NotificationRepository $notificationRepository,
    ) {
    }

    public function __invoke(): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $this->notificationRepository->deleteNotificationsForUser($user);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
