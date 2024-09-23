<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Parameter;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\Post;
use App\Api\Controller\AddUserEventController;
use App\Api\Controller\RemoveUserEventController;
use App\Entity\Contract\EventInterface;
use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use App\Repository\UserEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserEventRepository::class)]
#[ORM\Table(name: 'user_event')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => ['event_read']
            ],
            security: 'is_granted("ROLE_USER")',
        ),
        new Post(
            uriTemplate: '/user_events/add',
            controller: AddUserEventController::class,
            //            normalizationContext: [
            //                'groups' => ['user_event_add']
            //            ],
            //            denormalizationContext: [
            //                'groups' => ['user_event_add']
            //            ],
            //            security: 'is_granted("ROLE_USER") and object.user == user'
        ),
    ],
)]
class UserEvent implements ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected Ulid $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    protected User $user;

    #[ORM\ManyToOne(targetEntity: Event::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['event_read'])]
    protected ?Event $event = null;

    #[ORM\ManyToOne(targetEntity: CustomEvent::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'custom_event_id', referencedColumnName: 'id', nullable: true, onDelete: "CASCADE")]
    #[Groups(['event_read'])]
    protected ?CustomEvent $customEvent = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['user_event_read'])]
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

    public function getEvent(): EventInterface
    {
        return $this->event ?? $this->customEvent;
    }

    public function setEvent(EventInterface $event): static
    {
        match (true) {
            $event instanceof Event => $this->event = $event,
            $event instanceof CustomEvent => $this->customEvent = $event,
            default => true,
        };

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
