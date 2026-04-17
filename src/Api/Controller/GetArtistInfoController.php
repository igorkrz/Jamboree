<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\Artist;
use App\Entity\ArtistMediaObject;
use App\Factory\ArtistMediaObjectFactory;
use App\Manager\MediaManager;
use App\Repository\ArtistMediaObjectRepository;
use App\Repository\ArtistRepository;
use App\Service\Artist\SpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Storage\StorageInterface;
use function is_string;

final class GetArtistInfoController extends AbstractController
{
    public function __construct(
        private readonly SpotifyService $spotifyService,
        private readonly MediaManager $mediaManager,
        private readonly ArtistRepository $artistRepository,
        private readonly ArtistMediaObjectRepository $artistMediaObjectRepository,
        private readonly ArtistMediaObjectFactory $artistMediaObjectFactory,
        private readonly StorageInterface $storage,
    ) {
    }

    public function __invoke(Artist $artist): JsonResponse
    {
        if ($artist->getPicture() instanceof ArtistMediaObject) {
            return $this->json($artist, Response::HTTP_OK, [], ['groups' => ['artist:read']]);
        }

        $spotifyArtistInfoDto = $this->spotifyService->getArtistInfo($artist->getName());
        $imageUrl = $spotifyArtistInfoDto->imageUrl;

        if (is_string($imageUrl)) {
            $file = $this->mediaManager->createMediaObjectFileFromUrl($imageUrl);
            if (!$file instanceof File) {
                return $this->json($artist, Response::HTTP_OK, [], ['groups' => ['artist:read']]);
            }

            $picture = $this->artistMediaObjectFactory->create();
            $picture->setFile($file);
            $this->artistMediaObjectRepository->add($picture);
            $picture->setFilePath($this->storage->resolveUri($picture, 'file'));

            $artist->setPicture($picture);
            $this->artistRepository->add($artist);
        }

        return $this->json($artist, Response::HTTP_OK, [], ['groups' => ['artist:read']]);
    }
}
