<?php

declare(strict_types=1);

namespace App\Service\Scraper\Item;

use App\Service\Scraper\ItemScraperInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class DirtyOldItemScraper implements ItemScraperInterface
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    /**
     * @return string[]
     */
    public function scrape(string $url): array
    {
        try {
            $response = $this->httpClient->request('GET', $url);

            $htmlContent = $response->getContent();
            $crawler = new Crawler($htmlContent);

            $image = $crawler->filter('.woocommerce-product-gallery__image > a')->link()->getUri();

            $event = $crawler
                ->filter('.summary')
                ->each(function (Crawler $node) {
                    return [
                        'id' => $node->filter('.product_meta > .sku_wrapper >.sku')->text(),
                        'title' => $node->filter('.product_title')->text(),
                        'description' => $node->filter('.woocommerce-product-details__short-description')->text(),
                        'price' => $node->filter('.price')->text(),
                    ];
                })[0];
        } catch (ClientExceptionInterface $e) {
            throw new ClientException($e->getResponse());
        } catch (RedirectionExceptionInterface $e) {
            throw new RedirectionException($e->getResponse());
        } catch (ServerExceptionInterface $e) {
            throw new ServerException($e->getResponse());
        } catch (TransportExceptionInterface $e) {
            throw new TransportException($e->getMessage());
        }

        $pattern = '/\b(\d{1,2}\.\d{1,2}\.\d{4})\b|\b(\d{4}-\d{2}-\d{2})\b|\b(\d{1,2}\/\d{1,2}\/\d{4})\b|\b(\d{1,2}-\d{1,2}-\d{4})\b/';
        preg_match($pattern, $event['description'], $matches);

        if (count($matches) > 0) {
            $event['date'] = $matches[0];
        }

        $event['image'] = $image;

        return $event;
    }
}
