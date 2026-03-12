<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\ScraperProvider;
use App\Message\ScrapeItemMessage;
use App\Service\Scraper\List\HangtimeListScraper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ScrapeHangtimeCommand extends Command
{
    public function __construct(
        private readonly HangtimeListScraper $listScraper,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('app:scrape:hangtime-agency');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scrapedList = $this->listScraper->scrape();

        foreach ($scrapedList as $scrapedItem) {
            $this->messageBus->dispatch(new ScrapeItemMessage(
                url: $scrapedItem['url'],
                provider: ScraperProvider::HANGTIME_AGENCY,
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
