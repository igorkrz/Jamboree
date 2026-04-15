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

final class CinestarListScraper implements ListScraperInterface
{
    private const string URL = 'https://cinestarcinemas.hr/zagreb/';

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
                ->filter('.movie-item[data-genre="Anime"]')
                ->each(function (Crawler $node) {
                    $nameNode = $node->filter('.movie-desc > a > h2');
                    $nameNode->filter('span.filmlabel')->each(function (Crawler $n) {
                        $domNode = $n->getNode(0);
                        $domNode?->parentNode->removeChild($domNode);
                    });

                    $link = $node->filter('a')->link()->getUri();

                    $itemData = [
                        'internalCode' => str_replace(self::URL, '', $link),
                        'name' => trim($nameNode->text()),
                        'link' => $link,
                        'description' => $node->filter('p')->text(),
                        'imageUrl' => $node->filter('.img-fluid')->attr('src'),
                        'categories' => [['name' => 'Anime']],
                    ];

                    return ['url' => 'cinestar://' . base64_encode(json_encode($itemData))];
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
        return ScraperProvider::CINESTAR;
    }
}
