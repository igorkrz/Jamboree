<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Factory\AccessTokenFactory;
use App\Factory\UserFactory;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

final class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly TranslatorInterface $translator,
        private readonly AccessTokenFactory $accessTokenFactory,
        private readonly UserFactory $userFactory,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/api/security/register', name: 'api_security_register', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function register(Request $request): JsonResponse|ConstraintViolationListInterface
    {
        $user = $this->userFactory->create();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->submit(json_decode($request->getContent(), true));

        $violations = $this->validator->validate($form);

        if (count($violations) === 0) {
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('mailer@jamboree.com', 'JamboreeBot'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('views/security/confirmation_email.html.twig')
            );

            return $this->json('success');
        }

        return $this->json($violations, 422);
    }

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $this->userRepository->find($id);

        if (!$user instanceof User) {
            return $this->redirectToRoute('register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('register');
    }

    #[Route(path: '/api/security/login_check', name: 'api_security_login_check', methods: [Request::METHOD_POST])]
    public function login(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        if (!$user instanceof User) {
            throw new LogicException(sprintf(
                "Expected instance of %s, but got %s",
                User::class,
                $user
            ));
        }

        $accessToken = $this->accessTokenFactory->create()
            ->setUserIdentifier($user->getUserIdentifier())
            ->setHost($request->server->get('HTTP_USER_AGENT'))
            ->setIpAddress($request->getClientIp())
            ->setValidUntil(new DateTime('+1 hour'));

        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();

        $refreshToken = $this->accessTokenFactory->create();

        return $this->json([
            'user' => $user->getUserIdentifier(),
            'access_token' => $accessToken->getToken(),
            'refresh_token' => $refreshToken->getToken(),
        ]);
    }

    #[Route(path: '/api/security/login_state', name: 'api_security_login_state', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function loginState(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        return $this->json([$this->isGranted('ROLE_USER')]);
    }

    #[Route(path: '/api/security/logout', name: 'api_security_logout', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function logout(Request $request): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
