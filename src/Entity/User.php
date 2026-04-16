<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Contract\EventInterface;
use App\Entity\Contract\IdentifiableTrait;
use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use App\Enum\OAuthProvider;
use App\Repository\UserRepository;
use Deprecated;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ApiResource()]
class User implements ResourceInterface, TimestampableInterface, UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;
    use IdentifiableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected Ulid $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
    #[Groups(['user:read'])]
    protected ?string $email = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['user:read'])]
    protected ?string $firstName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['user:read'])]
    protected ?string $lastName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['user:read'])]
    protected ?string $picture = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $isVerified = false;

    #[ORM\Column(type: 'string')]
    protected ?string $password = null;

    /** @var string[] */
    #[ORM\Column(type: 'json')]
    protected array $roles = [];

    /**
     * @var Collection<array-key, UserEvent>
     */
    #[ORM\OneToMany(targetEntity: UserEvent::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'user_event')]
    protected Collection $events;

    /**
     * @var Collection<array-key, UserCalendar>
     */
    #[ORM\OneToMany(targetEntity: UserCalendar::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $calendars;

    /**
     * @var Collection<array-key, UserOAuthToken>
     */
    #[ORM\OneToMany(targetEntity: UserOAuthToken::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $oauthTokens;

    public function __construct(?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
        $this->events = new ArrayCollection();
        $this->calendars = new ArrayCollection();
        $this->oauthTokens = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email = null): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName = null): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName = null): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture = null): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getFullName(): ?string
    {
        $fullName = '';

        if ($this->firstName) {
            $fullName .= $this->firstName;
        }

        if ($this->lastName) {
            $fullName = ($fullName ? $fullName.' ' : '').$this->lastName;
        }

        return $fullName === '' ? null : $fullName;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int|string, UserEvent>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function hasEvent(EventInterface $event): bool
    {
        foreach ($this->getEvents() as $userEvent) {
            if ($userEvent->getEvent()->getObjectIdentifier() === $event->getObjectIdentifier()) {
                return true;
            }
        }

        return false;
    }

    public function addUserEvent(UserEvent $userEvent): static
    {
        if (!$this->hasEvent($userEvent->getEvent())) {
            $this->events->add($userEvent);
            $userEvent->setUser($this);
        }

        return $this;
    }

    public function removeUserEvent(UserEvent $userEvent): static
    {
        if ($this->hasEvent($userEvent->getEvent())) {
            $this->events->removeElement($userEvent);
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password = null): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    #[Deprecated]
    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getCalendars(): Collection
    {
        return $this->calendars;
    }

    public function addCalendar(UserCalendar $calendar): static
    {
        if (!$this->calendars->contains($calendar)) {
            $this->calendars->add($calendar);
            $calendar->setUser($this);
        }

        return $this;
    }

    public function removeCalendar(UserCalendar $calendar): static
    {
        $this->calendars->removeElement($calendar);

        return $this;
    }

    /**
     * @return Collection<array-key, UserOAuthToken>
     */
    public function getOAuthTokens(): Collection
    {
        return $this->oauthTokens;
    }

    public function addOAuthToken(UserOAuthToken $oauthToken): static
    {
        if (!$this->oauthTokens->contains($oauthToken)) {
            $this->oauthTokens->add($oauthToken);
            $oauthToken->setUser($this);
        }

        return $this;
    }

    public function removeOAuthToken(UserOAuthToken $oauthToken): static
    {
        $this->oauthTokens->removeElement($oauthToken);

        return $this;
    }

    #[Groups(['user:read'])]
    public function hasGoogleAccount(): bool
    {
        return $this->oauthTokens->exists(fn($key, UserOAuthToken $token) => $token->getProvider() === OAuthProvider::GOOGLE);
    }
}
