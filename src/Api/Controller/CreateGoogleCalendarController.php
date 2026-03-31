<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\User;
use App\Service\Google\GoogleCalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class CreateGoogleCalendarController extends AbstractController
{
    public function __construct(
        private readonly GoogleCalendarService $googleCalendarService,
    ) {
    }

    public function __invoke(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        if (!$user->getCalendars()->isEmpty()) {
            return $this->json(['error' => 'User already has a calendar'], Response::HTTP_BAD_REQUEST);
        }

        $this->googleCalendarService->createCalendar($user);
        return $this->json(['message' => 'Calendar created successfully']);
    }
}
