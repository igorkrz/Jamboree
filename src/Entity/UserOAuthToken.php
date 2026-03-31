<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contract\IdentifiableTrait;
use App\Entity\Contract\ResourceInterface;
use App\Enum\OAuthProvider;
use App\Repository\UserOAuthTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: UserOAuthTokenRepository::class)]
#[ORM\Table(name: 'user_oauth_token')]
class UserOAuthToken implements ResourceInterface
{
    use IdentifiableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid')]
    protected Ulid $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'oauthTokens')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\Column(type: 'string', length: 50, enumType: OAuthProvider::class)]
    protected OAuthProvider $provider;

    #[ORM\Column(type: 'text')]
    protected string $accessToken;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $refreshToken = null;

    #[ORM\Column(type: 'datetime_immutable')]
    protected DateTimeImmutable $expiresAt;

    public function __construct(?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getProvider(): OAuthProvider
    {
        return $this->provider;
    }

    public function setProvider(OAuthProvider $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}
