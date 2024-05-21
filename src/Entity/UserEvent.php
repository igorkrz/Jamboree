<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use App\Repository\UserEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserEventRepository::class)]
#[ORM\Table(name: 'user_event')]
class UserEvent implements ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected Ulid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    protected User $user;

    #[ORM\ManyToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    protected Event $event;

    #[ORM\Column(type: 'boolean', nullable: true)]
    protected ?bool $attending = null;

    public function __construct(Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
    }

    public function getId(): Ulid
    {
        return $this->id;
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

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function isAttending(): ?bool
    {
        return $this->attending;
    }

    public function setAttending(?bool $attending = null): static
    {
        $this->attending = $attending;

        return $this;
    }
}
