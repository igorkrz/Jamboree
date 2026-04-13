<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Dto\ArtistInfoDto;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

use function count;
use function in_array;
use function strtolower;
use function trim;

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

    public function getArtistInfo(string $artistName): ArtistInfoDto
    {
        if ($this->lastFmApiKey === '') {
            $this->logger->warning('Last.fm API key is missing. Skipping artist info enrichment.');

            return new ArtistInfoDto();
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
            $summary = $data['artist']['bio']['summary'] ?? null;
            $bio = $data['artist']['bio']['content'] ?? null;

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

            $this->logger->info('Last.fm artist info found', [
                'artist' => $artistName,
                'genres' => $genres,
                'has_bio' => $bio !== null,
            ]);

            return new ArtistInfoDto(
                genres: $genres,
                summary: $summary,
                bio: $bio,
            );
        } catch (Throwable $e) {
            $this->logger->error('Failed to fetch info from Last.fm', [
                'artist' => $artistName,
                'error' => $e->getMessage(),
            ]);

            return new ArtistInfoDto();
        }
    }
}
