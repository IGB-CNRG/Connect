<?php

namespace App\Service;

use App\Entity\Log;
use App\Entity\Person;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ActivityLogger implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, EntityManagerAware, SecurityAware;

    public function logPersonActivity(Person $person, string $message)
    {
        $owner = $this->security()->getUser();
        $log = (new Log())
            ->setPerson($person)
            ->setUser($owner)
            ->setText($message);
        $this->entityManager()->persist($log);
        $this->entityManager()->flush();
    }
}