<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Api\Controller\CalendarController;
use App\Api\Controller\CreateGoogleCalendarController;
use App\Api\Controller\DeleteGoogleCalendarController;
use App\Api\Controller\ExportCalendarController;
use App\Api\Controller\SyncGoogleCalendarController;
use App\Entity\Contract\IdentifiableTrait;
use App\Entity\Contract\ResourceInterface;
use App\Repository\UserCalendarRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserCalendarRepository::class)]
#[ORM\Table(name: 'user_calendar')]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: 'calendar',
            controller: CalendarController::class,
        ),
        new Get(
            uriTemplate: 'calendar/ics',
            controller: ExportCalendarController::class,
            security: 'is_granted("ROLE_USER")',
        ),
        new Post(
            uriTemplate: 'calendar/google',
            controller: CreateGoogleCalendarController::class,
            security: 'is_granted("ROLE_USER")',
        ),
        new Post(
            uriTemplate: 'calendar/google/sync',
            controller: SyncGoogleCalendarController::class,
            security: 'is_granted("ROLE_USER")',
        ),
        new Delete(
            uriTemplate: 'calendar/google',
            controller: DeleteGoogleCalendarController::class,
            security: 'is_granted("ROLE_USER")',
        )
    ],
)]
class UserCalendar implements ResourceInterface
{
    use IdentifiableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid')]
    protected Ulid $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'calendars')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\Column(type: 'string', length: 255)]
    protected string $calendarId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $summary = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $etag = null;

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

    public function getCalendarId(): string
    {
        return $this->calendarId;
    }

    public function setCalendarId(string $calendarId): static
    {
        $this->calendarId = $calendarId;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getEtag(): ?string
    {
        return $this->etag;
    }

    public function setEtag(?string $etag): static
    {
        $this->etag = $etag;

        return $this;
    }
}
