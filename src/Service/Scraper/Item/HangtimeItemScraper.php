<?php

declare(strict_types=1);

namespace App\Service\Scraper\Item;

use App\Entity\Dto\EventDto;
use App\Enum\ScraperProvider;
use App\Factory\EventFactory;
use App\Factory\LocationFactory;
use App\Service\ArtistExtractor;
use App\Service\Scraper\ItemScraperInterface;
use DateTime;
use ReflectionException;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class HangtimeItemScraper implements ItemScraperInterface
{
    private const string BASE_EVENT_URL = 'https://tickets.hangtimeagency.com/event-detail-hr/';
    private const string BASE_IMAGE_URL = 'https://ttcdn.b-cdn.net/images/Event/';

    public function __construct(
        private HttpClientInterface $httpClient,
        private EventFactory $eventFactory,
        private LocationFactory $locationFactory,
        private ArtistExtractor $artistExtractor,
    ) {
    }

    /**
     * @throws ReflectionException|DecodingExceptionInterface
     */
    public function scrape(string $url): EventDto
    {
        try {
            $response = $this->httpClient->request('GET', $url)->toArray()['Event'];

            $resolver = new OptionsResolver();
            $resolver->setDefaults([
                'internalCode' => null,
                'name' => null,
                'description' => null,
                'price' => null,
                'holdingDate' => null,
                'url' => null,
                'imageUrl' => null,
                'provider' => ScraperProvider::HANGTIME_AGENCY->value,
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
            'venue' => $response['Building']['ProfileName'],
            'city' => $response['AddressContact']['City'],
            'addressLine' => $response['AddressContact']['AddressLine'],
            'zipCode' => $response['AddressContact']['Zip'],
            'country' => $response['AddressContact']['Country'],
        ];

        $event = [
            'internalCode' => $response['_id'],
            'name' => $response['ProfileName'],
            'description' => $response['About'],
            'price' => $response['MinPrice'],
            'holdingDate' => new DateTime($response['Begin']),
            'url' => self::BASE_EVENT_URL . $response['_id'],
            'imageUrl' => self::BASE_IMAGE_URL . $response['_id'] . '/' . $response['ShareImage'] . '.jpg',
            'tags' => $response['Keywords'],
            'artists' => $this->artistExtractor->extractArtists($response['ProfileName']),
        ];

        $locationDto = $this->locationFactory->createDtoFromArray($locationResolver->resolve($location));
        $event['location'] = $locationDto;

        return $this->eventFactory->createDtoFromArray($resolver->resolve($event));
    }
}
