<?php

namespace App\Service;

use App\Entity\Log;
use App\Entity\Person;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ActivityLogger implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, EntityManagerAware, SecurityAware;

    const PERSON_FIELD = [
        'uin' => 'UIN',
        'netid' => 'netID',
        'isDrsTrainingComplete' => 'DRS training',
        'isIgbTrainingComplete' => 'IGB training',
    ];

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

    private function getEntityEditMessage($entity): string
    {
        $uow = $this->entityManager()->getUnitOfWork();
        $uow->computeChangeSets();
        $changeset = $uow->getEntityChangeSet($entity);
        $changes = [];
        foreach ($changeset as $field => $change) {
            if (array_key_exists($field, self::PERSON_FIELD)) {
                $fieldName = self::PERSON_FIELD[$field];
            } else {
                // convert camelCase to lower case by default
                $fieldName = strtolower(join(" ", preg_split('/(?=[A-Z])/', $field)));
            }
            $changes[] = sprintf("%s from '%s' to '%s'", $fieldName, $change[0], $change[1]);
        }
        return sprintf('Changed %s', join(', ', $changes));
    }

    public function logPersonEdit(Person $person)
    {
        $message = $this->getEntityEditMessage($person);
        $this->logPersonActivity($person, $message);
    }
}