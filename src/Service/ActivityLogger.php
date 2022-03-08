<?php

namespace App\Service;

use App\Attribute\Loggable;
use App\Entity\Log;
use App\Entity\Person;
use App\Entity\Theme;
use App\Entity\ThemeAffiliation;
use ReflectionException;
use ReflectionProperty;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ActivityLogger implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, EntityManagerAware, SecurityAware;

    public function logNewThemeAffiliation(ThemeAffiliation $themeAffiliation)
    {
        $endString = '';
        if ($themeAffiliation->getEndedAt()) {
            $endString = sprintf(' and ending %s', $themeAffiliation->getEndedAt()->format('n/j/Y'));
        }
        $this->logPersonActivity(
            $themeAffiliation->getPerson(),
            sprintf(
                'Added affiliation with theme %s (%s), beginning %s',
                $themeAffiliation->getTheme()->getShortName(),
                $themeAffiliation->getMemberCategory()->getName(),
                $themeAffiliation->getStartedAt()->format('n/j/Y')
            ) . $endString
        );
        $this->logThemeActivity(
            $themeAffiliation->getTheme(),
            sprintf(
                'Added member affiliation with %s (%s), beginning %s',
                $themeAffiliation->getPerson()->getName(),
                $themeAffiliation->getMemberCategory()->getName(),
                $themeAffiliation->getStartedAt()->format('n/j/Y')
            )
        );
    }

    public function logEndThemeAffiliation(ThemeAffiliation $themeAffiliation)
    {
        $this->logPersonActivity(
            $themeAffiliation->getPerson(),
            sprintf(
                "Ended theme affiliation with %s on %s",
                $themeAffiliation->getTheme()->getShortName(),
                $themeAffiliation->getEndedAt()->format('n/j/Y')
            )
        );
    }

    /** @noinspection PhpParamsInspection */
    public function logPersonActivity(Person $person, string $message)
    {
        $owner = $this->security()->getUser();
        $log = (new Log())
            ->setPerson($person)
            ->setUser($owner)
            ->setText($message);
        $this->entityManager()->persist($log);
    }

    /** @noinspection PhpParamsInspection */
    public function logThemeActivity(Theme $theme, string $message)
    {
        $owner = $this->security()->getUser();
        $log = (new Log())
            ->setTheme($theme)
            ->setUser($owner)
            ->setText($message);
        $this->entityManager()->persist($log);
    }

    private function getEntityEditMessage($entity): string
    {
        $uow = $this->entityManager()->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($entity);
        $changes = [];
        foreach ($changeSet as $field => $change) {
            try {
                $reflection = new ReflectionProperty($entity::class, $field);
                $loggableAttributes = $reflection->getAttributes(Loggable::class);
                if (count($loggableAttributes) > 0) {
                    $loggableArguments = $loggableAttributes[0]->getArguments();
                    if (array_key_exists('displayName', $loggableArguments)) {
                        $fieldName = $loggableArguments['displayName'];
                    } else {
                        // convert camelCase to lower case by default
                        $fieldName = strtolower(join(" ", preg_split('/(?=[A-Z])/', $field)));
                    }
                    if (!array_key_exists('details', $loggableArguments) || $loggableArguments['details'] === true) {
                        $changes[] = sprintf("%s from '%s' to '%s'", $fieldName, $change[0], $change[1]);
                    } else {
                        $changes[] = sprintf("%s", $fieldName);
                    }
                }
            } catch (ReflectionException) {
            }
        }
        return sprintf('Changed %s', join(', ', $changes));
    }

    public function logPersonEdit(Person $person)
    {
        $message = $this->getEntityEditMessage($person);
        $this->logPersonActivity($person, $message);
    }
}