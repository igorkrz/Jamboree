<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\CustomEvent;
use App\Entity\Location;
use App\Entity\User;
use App\Factory\CustomEventFactory;
use App\Factory\UserEventFactory;
use App\Form\CustomEventType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

final class CreateCustomEventController extends AbstractController
{
    public function __construct(
        private readonly CustomEventFactory $customEventFactory,
        private readonly LocationRepository $locationRepository,
        private readonly UserEventFactory $userEventFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly StorageInterface $storage,
    ) {
    }

    public function __invoke(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $customEvent = $this->customEventFactory->create();
        $form = $this->createForm(CustomEventType::class, $customEvent);

        $file = $this->getFile($request);
        $submittedData = $file === null ? $request->request->all() : $request->request->all() + $this->setFileData($file);

        $form->submit($submittedData);

        if (!$form->isValid()) {
            return $this->json($form->getErrors(), 422);
        }

        $customEvent
            ->setUser($user)
            ->setLocation($this->getOrCreateLocation($customEvent->getLocation()));

        $this->setPicture($customEvent, $file);

        $this->entityManager->persist($customEvent);
        $this->entityManager->flush();

        $userEvent = $this->userEventFactory->create();
        $userEvent
            ->setUser($user)
            ->setEvent($customEvent);

        $this->entityManager->persist($userEvent);
        $this->entityManager->flush();

        return $this->json(['custom_event' => $customEvent->getId()->toRfc4122()]);
    }

    private function getFile(Request $request): ?UploadedFile
    {
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            return null;
        }

        return $file;
    }

    /**
     * @return array<string, array<string, array<string, UploadedFile>>>|null
     */
    private function setFileData(?UploadedFile $file): ?array
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        return ['picture' => ['file' => ['file' => $file]]];
    }

    private function setPicture(CustomEvent $customEvent, ?UploadedFile $file = null): void
    {
        if (!$file instanceof UploadedFile) {
            $customEvent->setPicture();
            return;
        }

        $picture = $customEvent->getPicture();
        $this->entityManager->persist($picture);

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
