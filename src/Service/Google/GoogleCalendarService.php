<?php

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\Location;
use App\Entity\User;
use App\Entity\UserCalendar;
use App\Entity\UserEvent;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function array_chunk;
use function array_filter;
use function bin2hex;
use function implode;
use function json_encode;
use function random_bytes;

final class GoogleCalendarService
{
    private const string CALENDAR_API_URL = 'https://www.googleapis.com/calendar/v3/calendars';
    private const string CALENDAR_API_BATCH_URL = 'https://www.googleapis.com/batch/calendar/v3';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly GoogleAuthService $googleAuthService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createCalendar(User $user, string $summary = 'Jamboree'): void
    {
        $accessToken = $this->getValidAccessToken($user);

        $response = $this->httpClient->request(Request::METHOD_POST, self::CALENDAR_API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'summary' => $summary,
            ],
        ]);

        $data = $response->toArray();

        $userCalendar = $this->createUserCalendar($user, $data);
        $this->processBatchSync($user, $userCalendar->getCalendarId(), $accessToken);
    }

    public function syncEvents(User $user, UserCalendar $calendar): void
    {
        $accessToken = $this->getValidAccessToken($user);
        $this->clearCalendarEvents($calendar->getCalendarId(), $accessToken);
        $this->processBatchSync($user, $calendar->getCalendarId(), $accessToken);
    }

    public function deleteCalendar(User $user, UserCalendar $calendar): void
    {
        $accessToken = $this->getValidAccessToken($user);
        $this->httpClient->request('DELETE', self::CALENDAR_API_URL . '/' . $calendar->getCalendarId(), [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
    }

    private function clearCalendarEvents(string $calendarId, string $accessToken): void
    {
        $eventIds = [];
        $pageToken = null;

        do {
            $response = $this->httpClient->request('GET', self::CALENDAR_API_URL . '/' . $calendarId . '/events', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'query' => array_filter([
                    'pageToken' => $pageToken,
                    'fields' => 'items(id),nextPageToken',
                ]),
            ]);

            $data = $response->toArray();
            foreach ($data['items'] ?? [] as $item) {
                $eventIds[] = $item['id'];
            }
            $pageToken = $data['nextPageToken'] ?? null;
        } while ($pageToken);

        if (empty($eventIds)) {
            return;
        }

        foreach (array_chunk($eventIds, 50) as $chunk) {
            $boundary = 'batch_delete_' . bin2hex(random_bytes(16));
            $body = '';

            foreach ($chunk as $id) {
                $body .= "--$boundary\r\n";
                $body .= "Content-Type: application/http\r\n";
                $body .= "Content-ID: <delete:" . $id . ">\r\n\r\n";
                $body .= "DELETE /calendar/v3/calendars/" . $calendarId . "/events/" . $id . "\r\n\r\n";
            }
            $body .= "--$boundary--\r\n";

            $this->httpClient->request('POST', self::CALENDAR_API_BATCH_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'multipart/mixed; boundary=' . $boundary,
                ],
                'body' => $body,
            ]);
        }
    }

    private function processBatchSync(User $user, string $calendarId, string $accessToken): void
    {
        $events = [];
        foreach ($user->getEvents() as $userEvent) {
            $events[] = [
                'id' => $userEvent->getId()->toRfc4122(),
                'data' => $this->formatEvent($userEvent),
            ];
        }

        if (empty($events)) {
            return;
        }

        foreach (array_chunk($events, 50) as $chunk) {
            $boundary = 'batch_' . bin2hex(random_bytes(16));
            $body = '';

            foreach ($chunk as $event) {
                $body .= "--$boundary\r\n";
                $body .= "Content-Type: application/http\r\n";
                $body .= "Content-ID: <item:" . $event['id'] . ">\r\n\r\n";
                $body .= "POST /calendar/v3/calendars/" . $calendarId . "/events\r\n";
                $body .= "Content-Type: application/json\r\n\r\n";
                $body .= json_encode($event['data']) . "\r\n";
            }
            $body .= "--$boundary--\r\n";

            $this->httpClient->request('POST', self::CALENDAR_API_BATCH_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'multipart/mixed; boundary=' . $boundary,
                ],
                'body' => $body,
            ]);
        }
    }

    private function formatEvent(UserEvent $userEvent): array
    {
        $event = $userEvent->getEvent();
        $holdingDate = $event->getHoldingDate();

        if (!$holdingDate) {
            throw new InvalidArgumentException('Event must have a holding date');
        }

        $startDate = $holdingDate->format('Y-m-d');
        $endDate = (clone $holdingDate)->modify('+1 day')->format('Y-m-d');

        return [
            'summary' => $event->getName(),
            'description' => $event->getDescription(),
            'location' => $this->formatLocation($event->getLocation()),
            'start' => ['date' => $startDate],
            'end' => ['date' => $endDate],
        ];
    }

    private function formatLocation(?Location $location): string
    {
        if (!$location instanceof Location) {
            return '';
        }

        return implode(', ', array_filter([
            $location->getVenue(),
            $location->getAddressLine(),
            $location->getCity(),
            $location->getZipCode(),
            $location->getCountry(),
        ]));
    }

    /**
     * @param array{id: string, summary: string, etag: string} $data
     */
    private function createUserCalendar(User $user, array $data): UserCalendar
    {
        $userCalendar = new UserCalendar();
        $userCalendar
            ->setCalendarId($data['id'])
            ->setUser($user)
            ->setSummary($data['summary'])
            ->setEtag($data['etag']);

        $this->entityManager->persist($userCalendar);
        $this->entityManager->flush();

        return $userCalendar;
    }

    private function getValidAccessToken(User $user): string
    {
        $accessToken = $this->googleAuthService->getValidAccessToken($user);
        if (!$accessToken) {
            throw new RuntimeException('No valid Google access token found.');
        }

        return $accessToken;
    }
}
