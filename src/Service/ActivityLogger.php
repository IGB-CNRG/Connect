<?php

namespace App\Service;

use App\Attribute\Loggable;
use App\Entity\Key;
use App\Entity\KeyAffiliation;
use App\Entity\Log;
use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Entity\Theme;
use App\Entity\ThemeAffiliation;
use ReflectionException;
use ReflectionProperty;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ActivityLogger implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, EntityManagerAware, SecurityAware;

    public function logNewSupervisorAffiliation(SupervisorAffiliation $supervisorAffiliation)
    {
        $this->logPersonActivity(
            $supervisorAffiliation->getSupervisor(),
            sprintf('Added supervisee %s', $supervisorAffiliation->getSupervisee())
        );
        $this->logPersonActivity(
            $supervisorAffiliation->getSupervisee(),
            sprintf('Added supervisor %s', $supervisorAffiliation->getSupervisor()->getName())
        );
    }

    public function logNewThemeAffiliation(ThemeAffiliation $themeAffiliation)
    {
        $this->logPersonActivity(
            $themeAffiliation->getPerson(),
            sprintf(
                'Added affiliation with theme %s (%s)',
                $themeAffiliation->getTheme()->getShortName(),
                $themeAffiliation->getMemberCategory()->getName()
            )
        );
        $this->logThemeActivity(
            $themeAffiliation->getTheme(),
            sprintf(
                'Added member affiliation with %s (%s)',
                $themeAffiliation->getPerson()->getName(),
                $themeAffiliation->getMemberCategory()->getName()
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

    public function logNewKeyAffiliation(KeyAffiliation $keyAffiliation)
    {
        $this->logPersonActivity(
            $keyAffiliation->getPerson(),
            sprintf("Added key %s", $keyAffiliation->getCylinderKey()->getName())
        );
        $this->logKeyActivity(
            $keyAffiliation->getCylinderKey(),
            sprintf("Key given to %s", $keyAffiliation->getPerson())
        );
    }

    /** @noinspection PhpParamsInspection */
    public function logPersonActivity(Person $person, ?string $message)
    {
        if ($message) {
            $owner = $this->security()->getUser();
            $log = (new Log())
                ->setPerson($person)
                ->setUser($owner)
                ->setText($message);
            $this->entityManager()->persist($log);
        }
    }

    /** @noinspection PhpParamsInspection */
    public function logThemeActivity(Theme $theme, ?string $message)
    {
        if ($message) {
            $owner = $this->security()->getUser();
            $log = (new Log())
                ->setTheme($theme)
                ->setUser($owner)
                ->setText($message);
            $this->entityManager()->persist($log);
        }
    }

    private function getEntityEditMessage($entity, $messagePrefix = ''): ?string
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
                    if ($change[0] == null) {
                        if ($change[1] != null) {
                            // New
                            if (!array_key_exists('details', $loggableArguments)
                                || $loggableArguments['details'] === true) {
                                if (array_key_exists('type', $loggableArguments)
                                    && $loggableArguments['type'] == 'date') {
                                    $new = $change[1]->format('n/j/Y');
                                } else {
                                    $new = $change[1];
                                }
                                $changes[] = sprintf("added %s '%s'", $fieldName, $new);
                            } else {
                                $changes[] = sprintf("added %s", $fieldName);
                            }
                        }
                    } elseif ($change[1] == null) {
                        // Removed
                        $changes[] = sprintf("removed %s", $fieldName);
                    } else {
                        // Changed
                        if (!array_key_exists('details', $loggableArguments)
                            || $loggableArguments['details'] === true) {
                            if (array_key_exists('type', $loggableArguments) && $loggableArguments['type'] == 'date') {
                                $old = $change[0]->format('n/j/Y');
                                $new = $change[1]->format('n/j/Y');
                            } else {
                                $old = $change[0];
                                $new = $change[1];
                            }
                            $changes[] = sprintf("changed %s from '%s' to '%s'", $fieldName, $old, $new);
                        } else {
                            $changes[] = sprintf("changed %s", $fieldName);
                        }
                    }
                }
            } catch (ReflectionException) {
            }
        }
        if (count($changes) === 0) {
            return null;
        }
        return ucfirst(sprintf('%s%s', $messagePrefix, join(', ', $changes)));
    }

    public function logPersonEdit(Person $person)
    {
        $this->logPersonActivity($person, $this->getEntityEditMessage($person));
        $uow = $this->entityManager()->getUnitOfWork();
        $uow->computeChangeSets();

        // Log changes to key affiliations
        if ($person->getKeyAffiliations()->isDirty()) {
            $inserted = $person->getKeyAffiliations()->getInsertDiff();
            foreach ($inserted as $keyAffiliation) {
                $this->logNewKeyAffiliation($keyAffiliation);
            }
        }
        foreach ($person->getKeyAffiliations() as $keyAffiliation) {
            $this->logPersonActivity(
                $person,
                $this->getEntityEditMessage(
                    $keyAffiliation,
                    sprintf('Updated key assignment %s, ', $keyAffiliation->getCylinderKey()->getName())
                )
            );
            $this->logKeyActivity(
                $keyAffiliation->getCylinderKey(),
                $this->getEntityEditMessage($keyAffiliation, sprintf('Updated key assignment for %s, ', $person))
            );
        }

        // Log supervisor
        if ($person->getSupervisorAffiliations()->isDirty()) {
            $inserted = $person->getSupervisorAffiliations()->getInsertDiff();
            foreach ($inserted as $supervisorAffiliation) {
                $this->logNewSupervisorAffiliation($supervisorAffiliation);
            }
        }
        foreach ($person->getSupervisorAffiliations() as $supervisorAffiliation) {
            $this->logPersonActivity(
                $person,
                $this->getEntityEditMessage(
                    $supervisorAffiliation,
                    sprintf('Updated supervisor assignment %s, ', $supervisorAffiliation->getSupervisor()->getName())
                )
            );
            $this->logPersonActivity(
                $supervisorAffiliation->getSupervisor(),
                $this->getEntityEditMessage(
                    $supervisorAffiliation,
                    sprintf('Updated supervisee assignment %s, ', $person->getName())
                )
            );
        }

        // Log supervisees
        if ($person->getSuperviseeAffiliations()->isDirty()) {
            $inserted = $person->getSuperviseeAffiliations()->getInsertDiff();
            foreach ($inserted as $superviseeAffiliation) {
                $this->logNewSupervisorAffiliation($superviseeAffiliation);
            }
        }
        foreach ($person->getSuperviseeAffiliations() as $superviseeAffiliation) {
            $this->logPersonActivity(
                $person,
                $this->getEntityEditMessage(
                    $superviseeAffiliation,
                    sprintf('Updated supervisee assignment %s, ', $superviseeAffiliation->getSupervisee()->getName())
                )
            );
            $this->logPersonActivity(
                $superviseeAffiliation->getSupervisee(),
                $this->getEntityEditMessage(
                    $superviseeAffiliation,
                    sprintf('Updated supervisor assignment %s, ', $person->getName())
                )
            );
        }

        // Log theme changes
        if ($person->getThemeAffiliations()->isDirty()) {
            $inserted = $person->getThemeAffiliations()->getInsertDiff();
            foreach ($inserted as $themeAffiliation) {
                $this->logNewThemeAffiliation($themeAffiliation);
            }
        }
        foreach ($person->getThemeAffiliations() as $themeAffiliation) {
            $this->logPersonActivity(
                $person,
                $this->getEntityEditMessage(
                    $themeAffiliation,
                    sprintf('Updated theme affiliation with %s, ', $themeAffiliation->getTheme()->getShortName())
                )
            );
            $this->logThemeActivity(
                $themeAffiliation->getTheme(),
                $this->getEntityEditMessage($themeAffiliation, sprintf('Updated member affiliation for %s, ', $person))
            );
        }
        // todo log rooms
    }

    public function logKeyActivity(Key $key, string $message)
    {
        $owner = $this->security()->getUser();
        $log = (new Log())
            ->setCylinderKey($key)
            ->setUser($owner)
            ->setText($message);
        $this->entityManager()->persist($log);
    }
}