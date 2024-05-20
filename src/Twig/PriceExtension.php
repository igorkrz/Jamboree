<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PriceExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
        ];
    }

    public function formatPrice(float $number, int $decimals = 2, string $decPoint = '.', string $thousandsSep = ','): string
    {
        $price = number_format((float) $number, $decimals, $decPoint, $thousandsSep);
        $price .= '€';

        return $price;
    }
}
