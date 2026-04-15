<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\ScraperProvider;
use App\Message\ScrapeItemMessage;
use App\Service\Scraper\List\DirtyOldListScraper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function count;
use function sprintf;

#[AsCommand(name: 'app:scrape:dirty-old-shop', description: 'Scrape dirty old shop for events')]
final class ScrapeDirtyOldShopCommand extends Command
{
    public function __construct(
        private readonly DirtyOldListScraper $listScraper,
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
                provider: ScraperProvider::DIRTY_OLD_SHOP,
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
