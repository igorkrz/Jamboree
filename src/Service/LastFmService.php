<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

use function in_array;
use function strtolower;

final readonly class LastFmService
{
    private const string API_URL = 'https://ws.audioscrobbler.com/2.0/';

    private const array NOISE_TAGS = [
        'seen live',
        'favorite',
        'favourite',
        'amazing',
        'cool',
        'awesome',
        'love',
        'beautiful',
        'under 2000 listeners',
        'albums i own',
        'rock n roll',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $lastFmApiKey,
    ) {
    }

    /**
     * @return string[]
     */
    public function getArtistGenres(string $artistName): array
    {
        if ($this->lastFmApiKey === '') {
            $this->logger->warning('Last.fm API key is missing. Skipping genre enrichment.');

            return [];
        }

        try {
            $response = $this->httpClient->request('GET', self::API_URL, [
                'query' => [
                    'method' => 'artist.getInfo',
                    'artist' => $artistName,
                    'api_key' => $this->lastFmApiKey,
                    'format' => 'json',
                    'autocorrect' => 1,
                ],
            ]);

            $data = $response->toArray();
            $tags = $data['artist']['tags']['tag'] ?? [];

            $genres = [];
            foreach ($tags as $tag) {
                $tagName = strtolower(trim((string) ($tag['name'] ?? '')));
                if ($tagName === '' || in_array($tagName, self::NOISE_TAGS, true)) {
                    continue;
                }

                $genres[] = $tagName;

                if (count($genres) >= 5) {
                    break;
                }
            }

            $this->logger->info('Last.fm artist genres found', [
                'artist' => $artistName,
                'genres' => $genres,
            ]);

            return $genres;
        } catch (Throwable $e) {
            $this->logger->error('Failed to fetch genres from Last.fm', [
                'artist' => $artistName,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
