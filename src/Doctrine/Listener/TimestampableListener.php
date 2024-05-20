<?php

declare(strict_types=1);

namespace App\Doctrine\Listener;

use App\Entity\Contract\TimestampableInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(Events::prePersist)]
#[AsDoctrineListener(Events::preUpdate)]
class TimestampableListener
{
    public function prePersist(PrePersistEventArgs $eventArgs): void
    {
        $object = $eventArgs->getObject();

        if ($object instanceof TimestampableInterface) {
            $dateTime = new \DateTime();
            $object->setCreatedAt($dateTime);
            $object->setUpdatedAt($dateTime);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $object = $eventArgs->getObject();

        if ($object instanceof TimestampableInterface) {
            $object->setUpdatedAt(new \DateTime());
        }
    }
}
