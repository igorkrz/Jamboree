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

final readonly class EventimItemScraper implements ItemScraperInterface
{
    public function __construct(
        private EventFactory $eventFactory,
        private LocationFactory $locationFactory,
    ) {
    }

    public function scrape(string $url): EventDto
    {
        if (!str_starts_with($url, 'eventim://')) {
            throw new TransportException('Unsupported URL format: ' . $url);
        }

        $url = base64_decode(substr($url, 10));
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
            'holdingDate' => null,
            'url' => null,
            'imageUrl' => null,
            'provider' => ScraperProvider::EVENTIM->value,
            'location' => null,
            'tags' => [],
            'artists' => [],
        ]);

        $locationResolver = new OptionsResolver();
        $locationResolver->setDefaults([
            'venue' => null,
            'city' => null,
            'addressLine' => null,
            'zipCode' => null,
            'country' => null,
        ]);

        $location = [
            'venue' => $data['location']['name'] ?? null,
            'city' => $data['location']['city'] ?? null,
            'addressLine' => null,
            'zipCode' => $data['location']['postalCode'] ?? null,
            'country' => null,
        ];

        $event = [
            'internalCode' => (string) ($data['productId'] ?? $data['productGroupId'] ?? ''),
            'name' => $data['name'] ?? null,
            'description' => $data['groupName'] ?? $data['name'] ?? null,
            'price' => $price ?? '',
            'holdingDate' => isset($data['startDate']) ? new DateTime($data['startDate']) : null,
            'url' => $data['link'] ?? null,
            'imageUrl' => $data['imageUrl'] ?? null,
            'tags' => array_map(static fn (array $category) => $category['name'], $data['categories'] ?? []),
            'artists' => isset($data['groupName']) ? [$data['groupName']] : ($data['name'] ? [$data['name']] : []),
        ];

        $locationDto = $this->locationFactory->createDtoFromArray($locationResolver->resolve($location));
        $event['location'] = $locationDto;

        return $this->eventFactory->createDtoFromArray($resolver->resolve($event));
    }
}
