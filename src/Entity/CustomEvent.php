<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CustomEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomEventRepository::class)]
#[ORM\Table(name: 'custom_event')]
class CustomEvent extends AbstractEvent
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    protected User $user;

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
