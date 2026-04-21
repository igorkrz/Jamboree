<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Contract\EventInterface;
use App\Entity\Notification;
use App\Message\NewEventImportedMessage;
use App\Repository\EventRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[AsMessageHandler]
final readonly class NewEventImportedMessageHandler
{
    public function __construct(
        private EventRepository $eventRepository,
        private UserRepository $userRepository,
        private NotificationRepository $notificationRepository,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $discordWebhookUrl,
    ) {
    }

    public function __invoke(NewEventImportedMessage $message): void
    {
        $event = $this->eventRepository->find($message->eventId);
        if (!$event instanceof EventInterface) {
            return;
        }

        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $notification = new Notification();
            $notification->setUser($user);
            $notification->setTitle('New Event Imported');
            $notification->setMessage(sprintf('A new event "%s" has been imported.', $event->getName()));
            $notification->setData([
                'eventId' => $event->getId()->toRfc4122(),
                'eventName' => $event->getName(),
                'provider' => $event->getProvider()->getName(),
            ]);

            $this->notificationRepository->getEntityManager()->persist($notification);
        }

        $this->notificationRepository->getEntityManager()->flush();

        $this->sendDiscordNotification($event);
    }

    private function sendDiscordNotification(EventInterface $event): void
    {
        try {
            $this->httpClient->request('POST', $this->discordWebhookUrl, [
                'json' => [
                    'content' => sprintf('@everyone 🎵 **New Event Imported: %s**', $event->getName()),
                    'embeds' => [
                        [
                            'title' => $event->getName(),
                            'description' => $event->getDescription(),
                            'url' => $event->getUrl(),
                            'color' => 5814783,
                            'fields' => [
                                [
                                    'name' => 'Date',
                                    'value' => $event->getHoldingDate()?->format('Y-m-d') ?? 'N/A',
                                    'inline' => true,
                                ],
                                [
                                    'name' => 'Provider',
                                    'value' => $event->getProvider()->getName(),
                                    'inline' => true,
                                ],
                                [
                                    'name' => 'Price',
                                    'value' => $event->getPrice() ?: 'N/A',
                                    'inline' => true,
                                ],
                            ],
                            'image' => [
                                'url' => $event->getImageUrl(),
                            ],
                        ],
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            $this->logger->error('Failed to send Discord notification', [
                'error' => $e->getMessage(),
                'eventId' => $event->getId()->toRfc4122(),
            ]);
        }
    }
}
