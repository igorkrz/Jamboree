<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Event;
use App\Factory\EventFactory;
use App\Enum\ScraperProvider;
use App\Message\ScrapeItemMessage;
use App\Repository\EventRepository;
use App\Service\Scraper\Item\DirtyOldItemScraper;
use App\Service\Scraper\Item\EventimItemScraper;
use App\Service\Scraper\Item\HangtimeItemScraper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

use function gethostname;

#[AsMessageHandler]
final readonly class ScrapeItemMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private EventFactory $eventFactory,
        private DirtyOldItemScraper $dirtyOldItemScraper,
        private HangtimeItemScraper $hangtimeItemScraper,
        private EventimItemScraper $eventimItemScraper,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ScrapeItemMessage $message): void
    {
        $this->logger->info('Processing scrape message', [
            'provider' => $message->provider->value,
            'url' => $message->url,
            'worker' => gethostname(),
        ]);

        try {
            $scraper = match ($message->provider) {
                ScraperProvider::DIRTY_OLD_SHOP => $this->dirtyOldItemScraper,
                ScraperProvider::HANGTIME_AGENCY => $this->hangtimeItemScraper,
                ScraperProvider::EVENTIM => $this->eventimItemScraper,
            };

            $dto = $scraper->scrape($message->url);

            /** @var ?Event $event */
            $event = $this->eventRepository->findOneBy(['internalCode' => $dto->internalCode]);

            if ($event instanceof Event) {
                $event = $this->eventFactory->mapToExistingObject($dto, $event);
            } else {
                $event = $this->eventFactory->createFromDto($dto);
            }

            $this->entityManager->persist($event);
            $this->entityManager->flush();

            $this->logger->info('Successfully scraped item', [
                'internalCode' => $dto->internalCode,
                'provider' => $message->provider->value,
            ]);
        } catch (Throwable $e) {
            $this->logger->error('Failed to scrape item', [
                'provider' => $message->provider->value,
                'url' => $message->url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
