<?php

namespace App\Service\Scraper;

interface ItemScraperInterface
{
    /**
     * @return string[]
     */
    public function scrape(string $url): array;
}
