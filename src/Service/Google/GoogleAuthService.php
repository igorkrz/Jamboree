<?php

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\User;
use App\Entity\UserOAuthToken;
use App\Enum\OAuthProvider;
use App\Repository\UserOAuthTokenRepository;
use DateTimeImmutable;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function is_string;
use function usort;

final class GoogleAuthService
{
    private const string TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly UserOAuthTokenRepository $userOAuthTokenRepository,
        private readonly string $googleClientId,
        private readonly string $googleClientSecret,
    ) {
    }

    public function getValidAccessToken(User $user): ?string
    {
        $tokens = $user->getOAuthTokens()->filter(fn (UserOAuthToken $token) => $token->getProvider() === OAuthProvider::GOOGLE);
        if ($tokens->isEmpty()) {
            return null;
        }

        /** @var UserOAuthToken[] $sortedTokens */
        $sortedTokens = $tokens->toArray();
        usort($sortedTokens, fn (UserOAuthToken $a, UserOAuthToken $b) => $b->getExpiresAt() <=> $a->getExpiresAt());

        foreach ($sortedTokens as $token) {
            if ($token->getExpiresAt() > new DateTimeImmutable('+1 minute')) {
                return $token->getAccessToken();
            }

            $refreshToken = $token->getRefreshToken();
            if ($refreshToken) {
                $newAccessToken = $this->refreshAccessToken($token);
                if ($newAccessToken) {
                    return $newAccessToken;
                }
            }
        }

        return null;
    }

    private function refreshAccessToken(UserOAuthToken $token): ?string
    {
        try {
            $response = $this->httpClient->request('POST', self::TOKEN_URL, [
                'body' => [
                    'client_id' => $this->googleClientId,
                    'client_secret' => $this->googleClientSecret,
                    'refresh_token' => $token->getRefreshToken(),
                    'grant_type' => 'refresh_token',
                ],
            ]);

            $data = $response->toArray();

            $token->setAccessToken($data['access_token']);
            $token->setExpiresAt(new DateTimeImmutable('+' . (int) $data['expires_in'] . ' seconds'));
            if (isset($data['refresh_token'])) {
                $token->setRefreshToken($data['refresh_token']);
            }

            $this->userOAuthTokenRepository->add($token);

            return $token->getAccessToken();
        } catch (Exception) {
            return null;
        }
    }

    public function saveToken(User $user, AccessToken $accessToken): void
    {
        $refreshToken = $accessToken->getRefreshToken();
        $userToken = null;

        if (is_string($refreshToken)) {
            foreach ($user->getOAuthTokens() as $token) {
                if ($token->getProvider() === OAuthProvider::GOOGLE && $token->getRefreshToken() === $refreshToken) {
                    $userToken = $token;
                    break;
                }
            }
        }

        if (!$userToken instanceof UserOAuthToken) {
            $userToken = new UserOAuthToken();
            $userToken->setUser($user);
            $userToken->setProvider(OAuthProvider::GOOGLE);
            $user->addOAuthToken($userToken);
        }

        $userToken->setAccessToken($accessToken->getToken());
        if (is_string($refreshToken)) {
            $userToken->setRefreshToken($refreshToken);
        }

        $userToken->setExpiresAt(new DateTimeImmutable('+' . (int) $accessToken->getExpires() . ' seconds'));

        $this->userOAuthTokenRepository->add($userToken);
    }
}
