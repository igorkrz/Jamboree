<?php

declare(strict_types=1);

namespace App\Controller;

use App\Manager\MediaManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class MediaController extends AbstractController
{
    public function __construct(
        private readonly MediaManager $mediaManager,
    ) {
    }

    #[Route(path: '/images/events/{fileName}', name: 'get_event_picture', methods: [Request::METHOD_GET])]
    public function getEventPictureAction(string $fileName): BinaryFileResponse
    {
        return new BinaryFileResponse($this->mediaManager->warmupEventCache($fileName));
    }

    #[Route(path: '/images/custom_events/{fileName}', name: 'get_custom_event_picture', methods: [Request::METHOD_GET])]
    public function getCustomEventPictureAction(string $fileName): BinaryFileResponse
    {
        return new BinaryFileResponse($this->mediaManager->warmupCustomEventCache($fileName));
    }

    #[Route(path: '/images/artists/{fileName}', name: 'get_artist_picture', methods: [Request::METHOD_GET])]
    public function getArtistPictureAction(string $fileName): BinaryFileResponse
    {
        return new BinaryFileResponse($this->mediaManager->warmupArtistCache($fileName));
    }
}
