<?php

declare(strict_types=1);

namespace App\Service\Scraper;

interface ItemScraperInterface
{
    /**
     * @return string[]
     */
    public function scrape(string $url): array;
}
