<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use Psr\Log\LoggerInterface;

final readonly class ArtistTagEnricher
{
    public function __construct(
        private ArtistRepository $artistRepository,
        private LastFmService $lastFmService,
        private TagManager $tagManager,
        private LoggerInterface $logger,
    ) {
    }

    public function enrichArtist(string $artistName): Artist
    {
        $name = strtoupper($artistName);
        $artist = $this->getOrCreateArtist($name);
        if (is_string($artist->getBio())) {
            return $artist;
        }

        $artistInfo = $this->lastFmService->getArtistInfo($name);
        $genres = $artistInfo->genres;

        $artist->setSummary($artistInfo->summary);
        $artist->setBio($artistInfo->bio);

        if (empty($genres)) {
            $this->logger->warning('No genres found for artist', ['name' => $artistName]);
        } else {
            foreach ($genres as $genre) {
                $tag = $this->tagManager->getOrCreateTag($genre);
                $artist->addTag($tag);
            }

            $this->logger->info('Successfully enriched and saved artist to database', [
                'name' => $artistName,
                'genres' => $genres,
            ]);
        }

        $this->artistRepository->add($artist);

        return $artist;
    }

    private function getOrCreateArtist(string $artistName): Artist
    {
        $artist = $this->artistRepository->findOneBy(['name' => $artistName]);

        if ($artist instanceof Artist) {
            $this->logger->info('Found artist in database', ['name' => $artistName]);

            return $artist;
        }

        $artist = new Artist();
        $artist->setName($artistName);

        return $artist;
    }
}
