<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\User;
use App\Service\Google\GoogleCalendarService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DeleteGoogleCalendarController extends AbstractController
{
    public function __construct(
        private readonly GoogleCalendarService $googleCalendarService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        if ($user->getCalendars()->isEmpty()) {
            return $this->json(['error' => 'User does not have a calendar'], Response::HTTP_BAD_REQUEST);
        }

        $calendar = $user->getCalendars()->first();
        if ($calendar->getUser() !== $user) {
            return $this->json(['error' => 'User does not have this calendar'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->googleCalendarService->deleteCalendar($user, $calendar);
            $this->entityManager->remove($calendar);
            $this->entityManager->flush();
            return $this->json(['message' => 'Calendar deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->json(['error' => 'Sync failed: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
