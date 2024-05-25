<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements ResourceInterface, TimestampableInterface, UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected Ulid $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
    protected ?string $email = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $firstName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $lastName = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $isVerified = false;

    #[ORM\Column(type: 'string')]
    protected ?string $password = null;

    /** @var string[] */
    #[ORM\Column(type: 'json')]
    protected array $roles = [];

    /**
     * @var Collection<int|string, UserEvent>
     */
    #[ORM\OneToMany(targetEntity: UserEvent::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'user_event')]
    protected Collection $events;

    public function __construct(Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
        $this->events = new ArrayCollection();
    }

    public function getId(): Ulid
    {
        return $this->id;
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

    public function hasEvent(Event $event): bool
    {
        foreach ($this->getEvents() as $userEvent) {
            if ($userEvent->getEvent()->getId()->toRfc4122() === $event->getId()->toRfc4122()) {
                return true;
            }
        }

        return false;
    }

//    public function hasUserEvent(UserEvent $userEvent): ?string
//    {
//        if ($this->events->containsKey($userEvent->getId()->toRfc4122())) {
//            return $userEvent->getId()->toRfc4122();
//        }
//
//        if ($this->events->contains($userEvent)) {
//            return $userEvent->getId()->toRfc4122();
//        }
//
//        return null;
//    }

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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
}
