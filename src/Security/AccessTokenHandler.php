<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\AccessTokenRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

final readonly class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private AccessTokenRepository $accessTokenRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $accessToken = $this->accessTokenRepository->findOneBy(['token' => $accessToken]);

        if (null === $accessToken || !$accessToken->isValid()) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        return new UserBadge(
            $accessToken->getUserIdentifier(),
            fn (string $identifier) => $this->userRepository->findOneBy(['email' => $accessToken->getUserIdentifier()])
        );
    }
}
