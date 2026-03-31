<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\CustomEvent;
use App\Entity\Location;
use App\Entity\User;
use App\Factory\CustomEventFactory;
use App\Factory\UserEventFactory;
use App\Form\CustomEventType;
use App\Repository\CustomEventMediaObjectRepository;
use App\Repository\CustomEventRepository;
use App\Repository\LocationRepository;
use App\Repository\UserEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Vich\UploaderBundle\Storage\StorageInterface;

final class CreateCustomEventController extends AbstractController
{
    public function __construct(
        private readonly CustomEventFactory $customEventFactory,
        private readonly LocationRepository $locationRepository,
        private readonly UserEventFactory $userEventFactory,
        private readonly CustomEventRepository $customEventRepository,
        private readonly UserEventRepository $userEventRepository,
        private readonly CustomEventMediaObjectRepository $customEventMediaObjectRepository,
        private readonly StorageInterface $storage,
    ) {
    }

    public function __invoke(
        Request $request,
        #[CurrentUser] ?User $user = null,
        #[MapUploadedFile] ?UploadedFile $file = null,
    ): JsonResponse {
        $customEvent = $this->customEventFactory->create();
        $form = $this->createForm(CustomEventType::class, $customEvent);

        $submittedData = $request->request->all();

        if ($file instanceof UploadedFile) {
            $submittedData['picture']['file']['file'] = $file;
        }

        $form->submit($submittedData);

        if (!$form->isValid()) {
            return $this->json($form->getErrors(), 422);
        }

        $customEvent
            ->setUser($user)
            ->setLocation($this->getOrCreateLocation($customEvent->getLocation()));

        $this->setPicture($customEvent, $file);

        $this->customEventRepository->add($customEvent);

        $userEvent = $this->userEventFactory->create();
        $userEvent
            ->setUser($user)
            ->setEvent($customEvent);

        $this->userEventRepository->add($userEvent);

        return $this->json(['custom_event' => $customEvent->getId()->toRfc4122()]);
    }

    private function setPicture(CustomEvent $customEvent, ?UploadedFile $file = null): void
    {
        if (!$file instanceof UploadedFile) {
            $customEvent->setPicture();
            return;
        }

        $picture = $customEvent->getPicture();
        $this->customEventMediaObjectRepository->add($picture);

        $picture->setFilePath($this->storage->resolveUri($picture, 'file'));
    }

    private function getOrCreateLocation(Location $location): ?Location
    {
        if ($location->getVenue() === null) {
            return null;
        }

        $existingLocation = $this->locationRepository->findOneBy(['venue' => $location->getVenue()]);

        if ($existingLocation instanceof Location) {
            return $existingLocation;
        }

        return $location;
    }
}
