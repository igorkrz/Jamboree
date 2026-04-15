<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\ScrapeItemMessage;
use App\Service\Scraper\List\CinestarListScraper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function count;
use function sprintf;

#[AsCommand(name: 'app:scrape:cinestar', description: 'Scrape cinestar for anime movies')]
final class ScrapeCinestarCommand extends Command
{
    public function __construct(
        private readonly CinestarListScraper $listScraper,
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
                provider: $this->listScraper->getProvider(),
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
