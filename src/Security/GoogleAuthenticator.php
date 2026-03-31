<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Google\GoogleAuthService;
use Exception;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

use function bin2hex;
use function is_string;
use function random_bytes;
use function urlencode;

final class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private readonly GoogleClient $googleClient,
        private readonly GoogleAuthService $googleAuthService,
        private readonly JWTTokenManagerInterface $jwtTokenManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $accessToken = $this->fetchAccessToken($this->googleClient);

        try {
            /** @var GoogleUser $googleUser */
            $googleUser = $this->googleClient->fetchUserFromToken($accessToken);

            $email = $googleUser->getEmail();
            if (!is_string($email)) {
                throw new CustomUserMessageAuthenticationException('Email not provided by Google.');
            }

            $request->getPayload()->set('google_access_token', $accessToken->getToken());

            $passport = new SelfValidatingPassport(
                new UserBadge($email, function (string $userIdentifier) use ($googleUser, $accessToken) {
                    $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);

                    if (!$user instanceof User) {
                        $user = new User();
                        $user->setEmail($userIdentifier);
                        $user->setPassword(bin2hex(random_bytes(16)));
                    }

                    $user->setFirstName($googleUser->getFirstName());
                    $user->setLastName($googleUser->getLastName());
                    $user->setPicture($googleUser->getAvatar());
                    $user->setVerified($googleUser->getEmailVerified());

                    $this->userRepository->add($user);

                    $this->googleAuthService->saveToken($user, $accessToken);

                    return $user;
                }),
            );

            $passport->setAttribute('google_access_token', $accessToken->getToken());
            return $passport;
        } catch (Exception $e) {
            throw new CustomUserMessageAuthenticationException('Google authentication failed: ' . $e->getMessage());
        }
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $user = $passport->getUser();
        $jwtToken = $this->jwtTokenManager->createFromPayload($user, ['googleAccessToken' => $passport->getAttribute('google_access_token')]);

        return new JWTPostAuthenticationToken($user, $firewallName, ['ROLE_USER'], $jwtToken);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if (!$token instanceof JWTPostAuthenticationToken) {
            throw new UnauthorizedHttpException('JWT Token not found');
        }

        $targetUrl = '/login?token=' . urlencode($token->getCredentials());

        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse('/login');
    }
}
