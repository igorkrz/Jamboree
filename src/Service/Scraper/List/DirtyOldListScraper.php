<?php

declare(strict_types=1);

namespace App\Service\Scraper\List;

use App\Enum\ScraperProvider;
use App\Service\Scraper\ListScraperInterface;
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

final class DirtyOldListScraper implements ListScraperInterface
{
    private const string URL = 'https://www.dirtyoldempire.com/kategorija-proizvoda/karte/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @return array<string, string[]>
     */
    public function scrape(): array
    {
        try {
            $response = $this->httpClient->request('GET', self::URL);

            $htmlContent = $response->getContent();
            $crawler = new Crawler($htmlContent);

            $events = $crawler
                ->filter('.product')
                ->each(function (Crawler $node) {
                    return [
                        'name' => $node->filter('.woocommerce-loop-product__title')->text(),
                        'price' => $node->filter('.price')->text(),
                        'url' => $node->filter('.woocommerce-loop-product__link')->link()->getUri(),
                    ];
                });
        } catch (ClientExceptionInterface $e) {
            throw new ClientException($e->getResponse());
        } catch (RedirectionExceptionInterface $e) {
            throw new RedirectionException($e->getResponse());
        } catch (ServerExceptionInterface $e) {
            throw new ServerException($e->getResponse());
        } catch (TransportExceptionInterface $e) {
            throw new TransportException($e->getMessage());
        }

        return $events;
    }

    public function getProvider(): ScraperProvider
    {
        return ScraperProvider::DIRTY_OLD_SHOP;
    }
}
