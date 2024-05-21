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
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements ResourceInterface, TimestampableInterface
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

    /**
     * @return Collection<int|string, UserEvent>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function hasEvent(UserEvent $userEvent): ?string
    {
        if ($this->events->containsKey($userEvent->getId()->toRfc4122())) {
            return $userEvent->getId()->toRfc4122();
        }

        return null;
    }

    public function addEvent(UserEvent $userEvent): static
    {
        if (!$this->hasEvent($userEvent)) {
            $this->events->add($userEvent);
            $userEvent->setUser($this);
        }

        return $this;
    }

    public function removeEvent(UserEvent $userEvent): static
    {
        if ($this->hasEvent($userEvent)) {
            $this->events->removeElement($userEvent);
        }

        return $this;
    }
}
