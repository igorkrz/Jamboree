<?php

declare(strict_types=1);

namespace App\Service\Scraper;

interface ItemScraperInterface
{
    public function scrape(string $url): object;
}
