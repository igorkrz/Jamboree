<?php

declare(strict_types=1);

namespace App\Service\Artist;

use App\Entity\Dto\ArtistInfoDto;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

use function count;
use function is_string;

final readonly class SpotifyService
{
    private const string AUTH_URL = 'https://accounts.spotify.com/api/token';
    private const string API_URL = 'https://api.spotify.com/v1/';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $spotifyClientId,
        private string $spotifyClientSecret,
    ) {
    }

    public function getArtistInfo(string $artistName): ArtistInfoDto
    {
        if ($this->spotifyClientId === '' || $this->spotifyClientSecret === '') {
            $this->logger->warning('Spotify API credentials are missing. Skipping artist info enrichment.');
            return new ArtistInfoDto();
        }

        $accessToken = $this->getAccessToken();
        if (!is_string($accessToken)) {
            return new ArtistInfoDto();
        }

        try {
            $response = $this->httpClient->request('GET', self::API_URL . 'search', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'query' => [
                    'q' => $artistName,
                    'type' => 'artist',
                    'limit' => 1,
                ],
            ]);

            $data = $response->toArray();
            $artist = $data['artists']['items'][0] ?? null;

            if ($artist === null) {
                $this->logger->info('Spotify artist not found', ['artist' => $artistName]);
                return new ArtistInfoDto();
            }

            $images = $artist['images'] ?? [];
            $imageUrl = null;
            if (count($images) > 0) {
                $imageUrl = $images[0]['url'] ?? null;
            }

            $this->logger->info('Spotify artist info found', [
                'artist' => $artistName,
                'has_image' => $imageUrl !== null,
            ]);

            return new ArtistInfoDto(imageUrl: $imageUrl);
        } catch (Throwable $e) {
            $this->logger->error('Failed to fetch info from Spotify', [
                'artist' => $artistName,
                'error' => $e->getMessage(),
            ]);

            return new ArtistInfoDto();
        }
    }

    private function getAccessToken(): ?string
    {
        try {
            $response = $this->httpClient->request('POST', self::AUTH_URL, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'query' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->spotifyClientId,
                    'client_secret' => $this->spotifyClientSecret,
                ],
            ]);

            $data = $response->toArray();
            return $data['access_token'] ?? null;
        } catch (Throwable $e) {
            $this->logger->error('Failed to get Spotify access token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
