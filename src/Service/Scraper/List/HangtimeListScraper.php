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

use function preg_match;

final class HangtimeListScraper implements ListScraperInterface
{
    private const string URL = 'https://tickets.hangtimeagency.com/';

    private const string BASE_API_URL = 'https://api.tootoot.co/api/event/';

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
                ->filter('.tt-evt-li')
                ->each(function (Crawler $node) {
                    preg_match(
                        '#/([a-f0-9]{24})/?$#',
                        $node->filter('.tt-evt-li__name')->link()->getUri(),
                        $match
                    );
                    return [
                        'url' => self::BASE_API_URL . $match[0],
                        'internalCode' => $match[0],
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
        return ScraperProvider::HANGTIME_AGENCY;
    }
}
