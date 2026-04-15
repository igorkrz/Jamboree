<?php

declare(strict_types=1);

namespace App\Service\Scraper\List;

use App\Enum\ScraperProvider;
use App\Service\Scraper\ListScraperInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

use function base64_encode;
use function json_encode;

final class EventimListScraper implements ListScraperInterface
{
    private const string BASE_API_URL = 'https://public-api.eventim.com/websearch/search/api/exploration/v2/productGroups';

    public function __construct(private readonly HttpClientInterface $httpClient)
    {}

    public function scrape(): array
    {
        try {
            $responseHr = $this->httpClient->request('GET', self::BASE_API_URL, [
                'query' => [
                    'webId' => 'web__eventim-hrv',
                    'language' => 'hr',
                    'retail_partner' => 'HRY',
                    'categories' => 'Glazba|Metal',
                    'sort' => 'Recommendation',
                    'in_stock' => 'true',
                    'tags' => 'DISABLE_FBS',
                ]
            ]);

            $responseSi = $this->httpClient->request('GET', self::BASE_API_URL, [
                'query' => [
                    'webId' => 'web__eventim-svn',
                    'language' => 'en',
                    'retail_partner' => 'SIB',
                    'categories' => 'Music|Metal',
                    'sort' => 'Recommendation',
                    'in_stock' => 'true',
                    'tags' => 'DISABLE_FBS',
                ]
            ]);

            $productGroupsHr = $responseHr->toArray()['productGroups'] ?? [];
            $productGroupsSi = $responseSi->toArray()['productGroups'] ?? [];

            $productGroups = array_merge($productGroupsHr, $productGroupsSi);
            if (empty($productGroups)) {
                return [];
            }

            $items = [];
            foreach ($productGroups as $productGroup) {
                foreach ($productGroup['products'] ?? [] as $product) {
                    $itemData = [
                        'productId' => $product['productId'],
                        'productGroupId' => $productGroup['productGroupId'],
                        'name' => $product['name'],
                        'link' => $product['link'],
                        'location' => $product['typeAttributes']['liveEntertainment']['location'] ?? null,
                        'startDate' => $product['typeAttributes']['liveEntertainment']['startDate'] ?? $productGroup['startDate'],
                        'groupName' => $productGroup['name'],
                        'imageUrl' => $productGroup['imageUrl'],
                        'categories' => $productGroup['categories'] ?? [],
                    ];

                    $items[] = [
                        'url' => 'eventim://' . base64_encode(json_encode($itemData)),
                    ];
                }
            }

            return $items;
        } catch (Throwable $e) {
            throw new TransportException($e->getMessage());
        }
    }

    public function getProvider(): ScraperProvider
    {
        return ScraperProvider::EVENTIM;
    }
}
