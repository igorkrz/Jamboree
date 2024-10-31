<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Dto\EventDto;
use App\Entity\Event;
use App\Factory\EventFactory;
use App\Repository\EventRepository;
use App\Service\Scraper\Item\HangtimeItemScraper;
use App\Service\Scraper\List\HangtimeListScraper;
use AutoMapperPlus\Exception\UnregisteredMappingException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

final class ScrapeHangtimeCommand extends Command
{
    private int $created = 0;

    private int $updated = 0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRepository $eventRepository,
        private readonly EventFactory $eventFactory,
        private readonly HangtimeListScraper $listScraper,
        private readonly HangtimeItemScraper $itemScraper,
        private readonly LoggerInterface $logger,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('app:scrape:hangtime-agency');
    }

    /**
     * @throws UnregisteredMappingException|ReflectionException|DecodingExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scrapedList = $this->listScraper->scrape();

        foreach ($scrapedList as $scrapedItem) {
            $dto = $this->itemScraper->scrape($scrapedItem['url']);

            /** @var ?Event $event */
            $event = $this->eventRepository->findOneBy(['internalCode' => $dto->internalCode]);

            $event = $this->createOrUpdateEvent($dto, $event);

            $this->entityManager->persist($event);
            $this->entityManager->flush();
        }

        $entriesCount = count($scrapedList);

        $this->logger->info(sprintf(
            "Successfully imported %d entries, created %d and updated %d",
            $entriesCount,
            $this->created,
            $this->updated,
        ));

        return Command::SUCCESS;
    }

    /**
     * @throws UnregisteredMappingException
     */
    private function createOrUpdateEvent(EventDto $dto, ?Event $event = null): Event
    {
        if ($event instanceof Event) {
            /** @var Event $event */
            $event = $this->eventFactory->mapToExistingObject($dto, $event);
            ++$this->updated;

            return $event;
        }

        ++$this->created;

        return $this->eventFactory->createFromDto($dto);
    }
}
