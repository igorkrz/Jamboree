<?php

declare(strict_types=1);

namespace App\Controller;

use App\Manager\MediaManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $file = $this->mediaManager->warmupEventCache($fileName);

        return new BinaryFileResponse($file);
    }

    #[Route(path: '/images/custom_events/{fileName}', name: 'get_custom_event_picture', methods: [Request::METHOD_GET])]
    public function getCustomEventPictureAction(string $fileName): BinaryFileResponse
    {
        $file = $this->mediaManager->warmupCustomEventCache($fileName);

        return new BinaryFileResponse($file);
    }
}
