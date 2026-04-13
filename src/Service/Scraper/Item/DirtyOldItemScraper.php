<?php

declare(strict_types=1);

namespace App\Service\Scraper\Item;

use App\Entity\Dto\EventDto;
use App\Enum\ScraperProvider;
use App\Factory\EventFactory;
use App\Factory\LocationFactory;
use App\Service\Scraper\ItemScraperInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function count;
use function preg_match;
use function preg_replace;
use function strpos;
use function strlen;
use function substr;
use function trim;

final readonly class DirtyOldItemScraper implements ItemScraperInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private EventFactory $eventFactory,
        private LocationFactory $locationFactory,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \ReflectionException
     */
    public function scrape(string $url): EventDto
    {
        try {
            $response = $this->httpClient->request('GET', $url);

            $htmlContent = $response->getContent();
            $crawler = new Crawler($htmlContent);

            $resolver = new OptionsResolver();
            $resolver->setDefaults([
                'internalCode' => null,
                'name' => null,
                'description' => null,
                'price' => null,
                'holdingDate' => null,
                'url' => null,
                'imageUrl' => null,
                'provider' => ScraperProvider::DIRTY_OLD_SHOP->value,
                'location' => null,
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

            $image = $crawler->filter('.woocommerce-product-gallery__image > a')->link()->getUri();

            $event = $crawler
                ->filter('.summary')
                ->each(function (Crawler $node) {
                    return [
                        'internalCode' => $node->filter('.product_meta > .sku_wrapper >.sku')->text(),
                        'name' => $node->filter('.product_title')->text(),
                        'description' => $node->filter('.woocommerce-product-details__short-description')->text(),
                        'price' => (int) $node->filter('.price')->text(),
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

        $location = [
            'city' => 'Zagreb'
        ];

        $description = $event['description'];

        $pattern = '/\b(\d{1,2}\.\d{1,2}\.\d{4}|\d{1,2}\.\d{1,2}\.\d{2}|\d{1,2}\.\d{1,2}\.),?|\b(\d{4}-\d{2}-\d{2})\b,?|\b(\d{1,2}\/\d{1,2}\/\d{4})\b,?|\b(\d{1,2}-\d{1,2}-\d{4})\b,?/';
        preg_match($pattern, $description, $matches);

        if (count($matches) > 0) {
            $event['holdingDate'] = $matches[0];

            $datePosition = strpos($description, $matches[0]);
            $afterDate = trim(substr($description, $datePosition + strlen($matches[0])), " \n\r\t\v\0,.");

            $this->logger->info($afterDate);

            $locationPattern = '/^[\p{L}\s]+(?:\s*[–-])?/u';
            if (preg_match($locationPattern, $afterDate, $locationMatches)) {
                $cleanedString = preg_replace('/\s*[–-]\s*$/u', '', $locationMatches[0]);
                $location['venue'] = $cleanedString;
                $this->logger->info($location['venue']);
            }
        }

        $event['url'] = $url;
        $event['imageUrl'] = $image;
        $event['artists'] = [];

        $locationDto = $this->locationFactory->createDtoFromArray($locationResolver->resolve($location));
        $event['location'] = $locationDto;

        return $this->eventFactory->createDtoFromArray($resolver->resolve($event));
    }
}
