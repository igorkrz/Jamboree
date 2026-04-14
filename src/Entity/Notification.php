<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\Api\Controller\DeleteAllNotificationsController;
use App\Entity\Contract\IdentifiableTrait;
use App\Entity\Contract\ResourceInterface;
use App\Entity\Contract\TimestampableInterface;
use App\Entity\Contract\TimestampableTrait;
use App\Repository\NotificationRepository;
use ArrayObject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Ulid;

/**
 * @phpstan-type NotificationData array{
 *     eventId: string,
 *     eventName: string,
 *     provider: string,
 * }
 */
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
#[ApiResource(
    operations: [
        new GetCollection(
            order: ['createdAt' => 'DESC'],
            security: 'is_granted("ROLE_USER")',
        ),
        new Get(security: 'is_granted("ROLE_USER") and object.getUser() === user'),
        new Delete(security: 'is_granted("ROLE_USER") and object.getUser() === user'),
        new Post(
            uriTemplate: '/notifications/read-all',
            controller: DeleteAllNotificationsController::class,
            openapi: new Operation(
                responses: [
                    new Response(
                        description: 'Notifications deleted',
                        content: new ArrayObject(),
                    ),
                ],
                summary: 'Delete all notifications',
                description: 'Delete all notifications for the current user.',
            ),
            security: 'is_granted("ROLE_USER")',
        ),
    ],
    normalizationContext: ['groups' => ['notification:read']],
    denormalizationContext: ['groups' => ['notification:write']],
    order: ['createdAt' => 'DESC'],
)]
class Notification implements TimestampableInterface, ResourceInterface
{
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[Groups(['notification:read'])]
    protected Ulid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Groups(['notification:read'])]
    private string $title;

    #[ORM\Column(type: 'text', nullable: false)]
    #[Groups(['notification:read'])]
    private string $message;

    /**
     * @var ?NotificationData $data
     */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['notification:read'])]
    private ?array $data = null;

    public function __construct(?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return ?NotificationData
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param ?NotificationData $data
     */
    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
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
}
