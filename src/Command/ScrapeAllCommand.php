<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\ScrapeItemMessage;
use App\Service\Scraper\ListScraperInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\MessageBusInterface;

use function count;
use function sprintf;

#[AsCommand(name: 'app:scrape:all', description: 'Runs all available scrapers')]
final class ScrapeAllCommand extends Command
{
    /**
     * @param iterable<ListScraperInterface> $scrapers
     */
    public function __construct(
        #[AutowireIterator('app.list_scraper')]
        private readonly iterable $scrapers,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->scrapers as $scraper) {
            $this->logger->info(sprintf('Running scraper for provider: %s', $scraper->getProvider()->value));

            try {
                $scrapedList = $scraper->scrape();
                foreach ($scrapedList as $scrapedItem) {
                    $this->messageBus->dispatch(new ScrapeItemMessage(
                        url: $scrapedItem['url'],
                        provider: $scraper->getProvider(),
                    ));
                }

                $this->logger->info(sprintf(
                    'Successfully dispatched %d entries for provider: %s',
                    count($scrapedList),
                    $scraper->getProvider()->value
                ));
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Failed to run scraper for provider %s: %s',
                    $scraper->getProvider()->value,
                    $e->getMessage()
                ));
            }
        }

        return Command::SUCCESS;
    }
}
