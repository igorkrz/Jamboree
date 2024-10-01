<?php

namespace App\Api\Controller;

use App\Entity\CustomEventMediaObject;
use App\Entity\Location;
use App\Entity\User;
use App\Factory\CustomEventFactory;
use App\Factory\CustomEventMediaObjectFactory;
use App\Factory\LocationFactory;
use App\Factory\UserEventFactory;
use App\Form\CustomEventType;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateCustomEventController extends AbstractController
{
    private const GOOGLE_BUCKET_URL = 'https://storage.googleapis.com/jamboree-eu';

    public function __construct(
        private readonly CustomEventFactory $customEventFactory,
        private readonly CustomEventMediaObjectFactory $customEventMediaObjectFactory,
        private readonly LocationFactory $locationFactory,
        private readonly LocationRepository $locationRepository,
        private readonly UserEventFactory $userEventFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $location = $this->locationFactory->create();
        $locationForm = $this->createForm(LocationType::class, $location, ['allow_extra_fields' => true]);

        $locationForm->submit([
            'venue' => $request->get('venue'),
            'city' => $request->get('city')
        ]);

        $violations = $this->validator->validate($locationForm);

        if (count($violations) > 0) {
            return $this->json('fail', 422);
        }

        $location = $this->saveLocation($location);

        $customEvent = $this->customEventFactory->create();
        $form = $this->createForm(CustomEventType::class, $customEvent, ['allow_extra_fields' => true]);
        $form->submit($request->request->all());

        $violations = $this->validator->validate($form);

        if (count($violations) > 0) {
            return $this->json('fail', 422);
        }

        /** @var ?UploadedFile $file */
        $file = $request->files->get('file') ?? null;

        $picture = $this->createPicture($file);
        $filePath = $picture instanceof CustomEventMediaObject ? $picture->getFilePath() : null;

        $customEvent
            ->setUser($user)
            ->setPicture($picture)
            ->setImageUrl($filePath)
            ->setLocation($location);

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

    private function saveLocation(Location $location): Location
    {
        $existingLocation = $this->locationRepository->findOneBy(['venue' => $location->getVenue()]);

        if ($existingLocation instanceof Location) {

            return $existingLocation;
        }

        $this->entityManager->persist($location);
        $this->entityManager->flush();

        return $location;
    }

    private function createPicture(?UploadedFile $file): ?CustomEventMediaObject
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        $picture = $this->customEventMediaObjectFactory->create();
        $picture->setFile($file);

        $filePath = $this->getStorageFilePath($picture, $file);
        $picture->setFilePath($filePath);

        return $picture;
    }

    private function getStorageFilePath(CustomEventMediaObject $picture, ?File $file = null): ?string
    {
        if (!$file instanceof File) {
            return null;
        }

        return self::GOOGLE_BUCKET_URL . '/images/custom_events/' .
            $picture->getId() . '.' . $file->getExtension();
    }
}
