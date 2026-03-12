<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\ScraperProvider;

final readonly class ScrapeItemMessage
{
    public function __construct(
        public string $url,
        public ScraperProvider $provider,
    ) {
    }
}
