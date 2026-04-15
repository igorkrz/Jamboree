<?php

declare(strict_types=1);

namespace App\Service\Scraper\Item;

use App\Entity\Dto\EventDto;
use App\Enum\ScraperProvider;
use App\Factory\EventFactory;
use App\Factory\LocationFactory;
use App\Service\Scraper\ItemScraperInterface;
use DateTime;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function base64_decode;
use function json_decode;
use function str_starts_with;
use function substr;

final readonly class CinestarItemScraper implements ItemScraperInterface
{
    public function __construct(
        private EventFactory $eventFactory,
        private LocationFactory $locationFactory,
    ) {
    }

    public function scrape(string $url): EventDto
    {
        if (!str_starts_with($url, 'cinestar://')) {
            throw new TransportException('Unsupported URL format: ' . $url);
        }

        $url = base64_decode(substr($url, 11));
        if ($url === false) {
            throw new TransportException('Invalid URL format');
        }

        $data = json_decode($url, true, JSON_THROW_ON_ERROR);

        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'internalCode' => null,
            'name' => null,
            'description' => null,
            'price' => null,
            'holdingDate' => new DateTime('+1 month'),
            'url' => null,
            'imageUrl' => null,
            'isAiEnriched' => true,
            'provider' => ScraperProvider::CINESTAR->value,
            'location' => null,
            'tags' => [],
            'artists' => [],
        ]);

        $locationResolver = new OptionsResolver();
        $locationResolver->setDefaults([
            'venue' => 'Cinestar',
            'city' => 'Zagreb',
            'addressLine' => 'Ulica kneza Branimira 29',
            'zipCode' => '10000',
            'country' => 'Croatia',
        ]);

        $event = [
            'internalCode' => $data['internalCode'] ?? '',
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'url' => $data['link'] ?? null,
            'imageUrl' => $data['imageUrl'] ?? null,
            'tags' => array_map(static fn (array $category) => $category['name'], $data['categories'] ?? []),
        ];

        $locationDto = $this->locationFactory->createDtoFromArray($locationResolver->resolve());
        $event['location'] = $locationDto;

        return $this->eventFactory->createDtoFromArray($resolver->resolve($event));
    }
}
