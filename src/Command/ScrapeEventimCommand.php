<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\ScraperProvider;
use App\Message\ScrapeItemMessage;
use App\Service\Scraper\List\EventimListScraper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function count;
use function sprintf;

#[AsCommand(name: 'app:scrape:eventim', description: 'Scrape eventim for metal events')]
final class ScrapeEventimCommand extends Command
{
    public function __construct(
        private readonly EventimListScraper $listScraper,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scrapedList = $this->listScraper->scrape();

        foreach ($scrapedList as $scrapedItem) {
            $this->messageBus->dispatch(new ScrapeItemMessage(
                url: $scrapedItem['url'],
                provider: ScraperProvider::EVENTIM,
            ));
        }

        $entriesCount = count($scrapedList);

        $this->logger->info(sprintf(
            "Successfully dispatched %d entries for import",
            $entriesCount,
        ));

        return Command::SUCCESS;
    }
}
