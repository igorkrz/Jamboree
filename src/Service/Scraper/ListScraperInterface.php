<?php

namespace App\Service\Scraper;

interface ListScraperInterface
{
    /**
     * @return array<string, string[]>
     */
    public function scrape(): array;
}
