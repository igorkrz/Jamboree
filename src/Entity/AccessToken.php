<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contract\IdentifiableTrait;
use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use App\Repository\AccessTokenRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Faker\Provider\Uuid;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
#[ORM\Table(name: 'user_access_token')]
class AccessToken implements ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;
    use IdentifiableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected Ulid $id;

    #[ORM\Column(type: 'string')]
    private string $token;

    #[ORM\Column(type: 'string')]
    private string $userIdentifier;

    #[ORM\Column(type: 'string')]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'string')]
    private ?string $host = null;

    #[ORM\Column(type: 'datetimetz')]
    private DateTime $validUntil;

    public function __construct(?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();

        if (!isset($this->token)) {
            $this->token = Uuid::uuid();
        }
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(string $userIdentifier): static
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function isValid(): bool
    {
        $dateTime = new DateTime();

        return $this->validUntil >= $dateTime;
    }

    public function getValidUntil(): DateTime
    {
        return $this->validUntil;
    }

    public function setValidUntil(DateTime $validUntil): static
    {
        $this->validUntil = $validUntil;

        return $this;
    }
}
