<?php

declare(strict_types=1);

namespace App\Service\Scraper;

interface ListScraperInterface
{
    /**
     * @return array<string, string[]>
     */
    public function scrape(): array;
}
