<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\User;
use App\Service\CalendarExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ExportCalendarController extends AbstractController
{
    public function __construct(
        private readonly CalendarExportService $calendarExportService,
    ) {
    }

    public function __invoke(#[CurrentUser] User $user, Request $request): Response
    {
        return new Response($this->calendarExportService->exportToIcal($user->getEvents()), Response::HTTP_OK, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="jamboree_calendar.ics"',
        ]);
    }
}
