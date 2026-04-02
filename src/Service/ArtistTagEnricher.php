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
        $artist = $this->artistRepository->findOneBy(['name' => $artistName]);

        if ($artist instanceof Artist) {
            $this->logger->info('Found artist in database', ['name' => $artistName]);

            return $artist;
        }

        $this->logger->info('Artist not found in database, querying Last.fm', ['name' => $artistName]);
        $genres = $this->lastFmService->getArtistGenres($artistName);

        $artist = new Artist();
        $artist->setName($artistName);

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
}
