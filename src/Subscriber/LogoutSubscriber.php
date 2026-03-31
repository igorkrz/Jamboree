<?php

declare(strict_types=1);

namespace App\Subscriber;

use App\Entity\UserOAuthToken;
use App\Repository\UserOAuthTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

use function is_array;
use function is_string;

final readonly class LogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private JWTTokenManagerInterface $jwtTokenManager,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager,
        private UserOAuthTokenRepository $userOAuthTokenRepository,
    ) {
    }

    /**
     * @return array<class-string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if (!$token instanceof JWTPostAuthenticationToken) {
            return;
        }

        $decoded = $this->jwtTokenManager->decode($token);
        if (!is_array($decoded)) {
            return;
        }

        if (!isset($decoded['googleAccessToken'])) {
            return;
        }

        $googleAccessToken = $decoded['googleAccessToken'];
        if (!is_string($googleAccessToken)) {
            return;
        }

        $oauthToken = $this->userOAuthTokenRepository->findOneBy(['accessToken' => $googleAccessToken]);
        if (!$oauthToken instanceof UserOAuthToken) {
            return;
        }

        $this->entityManager->remove($oauthToken);
        $this->entityManager->flush();

        $response = new RedirectResponse($this->urlGenerator->generate('home'));

        $event->setResponse($response);
    }
}
