<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Dto\ArtistDto;
use App\Entity\Dto\EventDto;
use App\Entity\Event;
use App\Factory\EventFactory;
use App\Enum\ScraperProvider;
use App\Message\ScrapeItemMessage;
use App\Message\NewEventImportedMessage;
use App\Repository\EventRepository;
use App\Service\AiEventParser;
use App\Service\ArtistTagEnricher;
use App\Service\Scraper\Item\CinestarItemScraper;
use App\Service\Scraper\Item\DirtyOldItemScraper;
use App\Service\Scraper\Item\EventimItemScraper;
use App\Service\Scraper\Item\HangtimeItemScraper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

use function gethostname;
use function is_string;

#[AsMessageHandler]
final readonly class ScrapeItemMessageHandler
{
    public function __construct(
        private EventRepository $eventRepository,
        private EventFactory $eventFactory,
        private DirtyOldItemScraper $dirtyOldItemScraper,
        private HangtimeItemScraper $hangtimeItemScraper,
        private EventimItemScraper $eventimItemScraper,
        private CinestarItemScraper $cinestarItemScraper,
        private ArtistTagEnricher $artistTagEnricher,
        private LoggerInterface $logger,
        private AiEventParser $aiEventParser,
        private MessageBusInterface $messageBus,
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
                ScraperProvider::CINESTAR => $this->cinestarItemScraper,
            };

            $dto = $scraper->scrape($message->url);
            $event = $this->eventRepository->findOneBy(['internalCode' => $dto->internalCode]);
            $isNew = $event === null;
            if ($event instanceof Event && $event->isAiEnriched()) {
                $this->logger->info('Event already enriched with AI, skipping update', [
                    'internalCode' => $event->getInternalCode(),
                    'url' => $event->getUrl(),
                ]);

                return;
            }

            if ($dto->provider !== ScraperProvider::CINESTAR->value) {
                $dto = $this->aiEventParser->parse($dto);
            }

            $event = $this->createOrUpdateEvent($dto, $event);

            $this->extractArtistTags($event, $dto->artists);

            $this->eventRepository->add($event);

            if ($isNew) {
                $this->messageBus->dispatch(new NewEventImportedMessage($event->getId()));
            }

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

    private function createOrUpdateEvent(EventDto $dto, ?Event $event = null): Event
    {
        if ($event instanceof Event) {
            return $this->eventFactory->mapToExistingObject($dto, $event);
        }

        return $this->eventFactory->createFromDto($dto);
    }

    /**
     * @param ArtistDto[] $artists
     */
    private function extractArtistTags(Event $event, array $artists = []): void
    {
        foreach ($artists as $artistDto) {
            $artistName = $artistDto->name;
            if (!is_string($artistName)) {
                continue;
            }

            $artist = $this->artistTagEnricher->enrichArtist($artistName);
            $event->addArtist($artist);

            foreach ($artist->getTags() as $tag) {
                $event->addTag($tag);
            }
        }
    }
}
