<?php

declare(strict_types=1);

namespace App\Service\Scraper;

use App\Enum\ScraperProvider;

interface ListScraperInterface
{
    /**
     * @return array<string, string[]>
     */
    public function scrape(): array;

    public function getProvider(): ScraperProvider;
}
