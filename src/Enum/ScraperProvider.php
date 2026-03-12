<?php

declare(strict_types=1);

namespace App\Enum;

enum ScraperProvider: string
{
    case DIRTY_OLD_SHOP = 'dirty_old_shop';
    case HANGTIME_AGENCY= 'hangtime_agency';
}
