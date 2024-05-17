<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Service\Scraper\Item\DirtyOldItemScraper;
use App\Service\Scraper\List\DirtyOldListScraper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeEventsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRepository $eventRepository,
        private readonly DirtyOldListScraper $listScraper,
        private readonly DirtyOldItemScraper $itemScraper,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('app:scrape-events');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scrapedList = $this->listScraper->scrape();

        foreach ($scrapedList as $scrapedItem) {
            $scrapedEvent = $this->itemScraper->scrape($scrapedItem['url']);

            $event = $this->eventRepository->findOneBy(['internalCode' => $scrapedEvent['id']]);

            if (!$event instanceof Event) {
                $event = new Event();
            }

            try {
                $date = isset($scrapedEvent['date']) ? new \DateTime($scrapedEvent['date']) : null;
            } catch (\Exception $e) {
                $date = null;
            }

            $event
                ->setInternalCode($scrapedEvent['id'] ?? null)
                ->setUrl($scrapedItem['url'] ?? null)
                ->setImageUrl($scrapedEvent['image'] ?? null)
                ->setName($scrapedEvent['title'] ?? null)
                ->setDescription($scrapedEvent['description'] ?? null)
                ->setPrice($scrapedEvent['price'] ?? null)
                ->setHoldingDate($date);

            $this->entityManager->persist($event);
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
