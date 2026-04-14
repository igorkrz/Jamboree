<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Contract\EventInterface;
use App\Entity\Notification;
use App\Message\NewEventImportedMessage;
use App\Repository\EventRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class NewEventImportedMessageHandler
{
    public function __construct(
        private EventRepository $eventRepository,
        private UserRepository $userRepository,
        private NotificationRepository $notificationRepository,
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
    }
}
