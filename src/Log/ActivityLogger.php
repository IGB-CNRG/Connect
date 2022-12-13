<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Log;

use App\Attribute\Loggable;
use App\Entity\DepartmentAffiliation;
use App\Entity\KeyAffiliation;
use App\Entity\Log;
use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Entity\SupervisorAffiliation;
use App\Entity\ThemeAffiliation;
use App\Service\EntityManagerAware;
use App\Service\SecurityAware;
use ReflectionException;
use ReflectionProperty;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ActivityLogger implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, EntityManagerAware, SecurityAware;

    private const DATE_FORMAT = 'n/j/Y';

    public function logNewDepartmentAffiliation(DepartmentAffiliation $departmentAffiliation)
    {
        $this->log(
            $departmentAffiliation->getPerson(),
            sprintf(
                'Added department %s',
                $departmentAffiliation->getDepartment() ?? $departmentAffiliation->getOtherDepartment()
            )
        );
        if ($departmentAffiliation->getDepartment()) {
            $this->log(
                $departmentAffiliation->getDepartment(),
                sprintf('Added person %s', $departmentAffiliation->getPerson())
            );
        }
    }

    public function logEndDepartmentAffiliation(DepartmentAffiliation $departmentAffiliation)
    {
        $this->log(
            $departmentAffiliation->getPerson(),
            sprintf(
                "Ended department affiliation with %s on %s",
                $departmentAffiliation->getDepartment() ?? $departmentAffiliation->getOtherDepartment(),
                $departmentAffiliation->getEndedAt()->format(self::DATE_FORMAT)
            )
        );
        if ($departmentAffiliation->getDepartment()) {
            $this->log(
                $departmentAffiliation->getDepartment(),
                sprintf(
                    "Ended affiliation with %s on %s",
                    $departmentAffiliation->getPerson(),
                    $departmentAffiliation->getEndedAt()->format(self::DATE_FORMAT)
                )
            );
        }
    }

    public function logNewRoomAffiliation(RoomAffiliation $roomAffiliation)
    {
        $this->log($roomAffiliation->getPerson(), sprintf('Added room %s', $roomAffiliation->getRoom()));
        $this->log($roomAffiliation->getRoom(), sprintf('Added person %s', $roomAffiliation->getPerson()));
    }

    public function logEndRoomAffiliation(RoomAffiliation $roomAffiliation)
    {
        $this->log(
            $roomAffiliation->getPerson(),
            sprintf(
                "Ended room affiliation with %s on %s",
                $roomAffiliation->getRoom(),
                $roomAffiliation->getEndedAt()->format(self::DATE_FORMAT)
            )
        );
        $this->log(
            $roomAffiliation->getRoom(),
            sprintf(
                "Ended affiliation with %s on %s",
                $roomAffiliation->getPerson(),
                $roomAffiliation->getEndedAt()->format(self::DATE_FORMAT)
            )
        );
    }

    public function logNewSupervisorAffiliation(SupervisorAffiliation $supervisorAffiliation)
    {
        $this->log(
            $supervisorAffiliation->getSupervisor(),
            sprintf('Added supervisee %s', $supervisorAffiliation->getSupervisee())
        );
        $this->log(
            $supervisorAffiliation->getSupervisee(),
            sprintf('Added supervisor %s', $supervisorAffiliation->getSupervisor()->getName())
        );
    }

    public function logEndSupervisorAffiliation(SupervisorAffiliation $supervisorAffiliation)
    {
        $this->log(
            $supervisorAffiliation->getSupervisor(),
            sprintf(
                "Ended supervisee affiliation with %s on %s",
                $supervisorAffiliation->getSupervisee(),
                $supervisorAffiliation->getEndedAt()->format(self::DATE_FORMAT)
            )
        );
        $this->log(
            $supervisorAffiliation->getSupervisee(),
            sprintf(
                "Ended supervisor affiliation with %s on %s",
                $supervisorAffiliation->getSupervisor(),
                $supervisorAffiliation->getEndedAt()->format(self::DATE_FORMAT)
            )
        );
    }

    public function logNewThemeAffiliation(ThemeAffiliation $themeAffiliation)
    {
        $this->log(
            $themeAffiliation->getPerson(),
            sprintf(
                'Added affiliation with theme %s (%s)',
                $themeAffiliation->getTheme()->getShortName(),
                $themeAffiliation->getMemberCategory()->getName()
            )
        );
        $this->log(
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
        $this->log(
            $themeAffiliation->getPerson(),
            sprintf(
                "Ended theme affiliation with %s on %s. Exit reason: \"%s\"",
                $themeAffiliation->getTheme()->getShortName(),
                $themeAffiliation->getEndedAt()->format(self::DATE_FORMAT),
                $themeAffiliation->getExitReason()
            )
        );
    }

    public function logNewKeyAffiliation(KeyAffiliation $keyAffiliation)
    {
        $this->log(
            $keyAffiliation->getPerson(),
            sprintf("Added key %s", $keyAffiliation->getCylinderKey()->getName())
        );
        $this->log(
            $keyAffiliation->getCylinderKey(),
            sprintf("Key given to %s", $keyAffiliation->getPerson())
        );
    }

    public function logPersonEdit(Person $person)
    {
        $this->log($person, $this->getEntityEditMessage($person));
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
            $this->log(
                $person,
                $this->getEntityEditMessage(
                    $keyAffiliation,
                    sprintf('Updated key assignment %s, ', $keyAffiliation->getCylinderKey()->getName())
                )
            );
            $this->log(
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
            $this->log(
                $person,
                $this->getEntityEditMessage(
                    $supervisorAffiliation,
                    sprintf('Updated supervisor assignment %s, ', $supervisorAffiliation->getSupervisor()->getName())
                )
            );
            $this->log(
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
            $this->log(
                $person,
                $this->getEntityEditMessage(
                    $superviseeAffiliation,
                    sprintf('Updated supervisee assignment %s, ', $superviseeAffiliation->getSupervisee()->getName())
                )
            );
            $this->log(
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
            $this->log(
                $person,
                $this->getEntityEditMessage(
                    $themeAffiliation,
                    sprintf('Updated theme affiliation with %s, ', $themeAffiliation->getTheme()->getShortName())
                )
            );
            $this->log(
                $themeAffiliation->getTheme(),
                $this->getEntityEditMessage($themeAffiliation, sprintf('Updated member affiliation for %s, ', $person))
            );
        }
        // Log room changes
        if ($person->getRoomAffiliations()->isDirty()) {
            $inserted = $person->getRoomAffiliations()->getInsertDiff();
            foreach ($inserted as $roomAffiliation) {
                $this->logNewRoomAffiliation($roomAffiliation);
            }
        }
        foreach ($person->getRoomAffiliations() as $roomAffiliation) {
            $this->log(
                $person,
                $this->getEntityEditMessage(
                    $roomAffiliation,
                    sprintf('Updated room affiliation with %s, ', $roomAffiliation->getRoom())
                )
            );
            $this->log(
                $roomAffiliation->getRoom(),
                $this->getEntityEditMessage($roomAffiliation, sprintf('Updated member affiliation for %s, ', $person))
            );
        }
    }

    /* Entity Logging Function */

    public function log(LogSubjectInterface $subject, ?string $message)
    {
        if($message){
            $log = (new Log())
                ->setText($message)
                ->setUser($this->security()->getUser());
            $subject->addLog($log);
            $this->entityManager()->persist($log);
        }
    }

    /* Helpers */
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
                                    $new = $change[1]->format(self::DATE_FORMAT);
                                } elseif (array_key_exists('type', $loggableArguments)
                                          && $loggableArguments['type'] == 'array') {
                                    $new = '[' . join(', ', $change[1]) . ']';
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
                                $old = $change[0]->format(self::DATE_FORMAT);
                                $new = $change[1]->format(self::DATE_FORMAT);
                            } elseif (array_key_exists('type', $loggableArguments)
                                      && $loggableArguments['type'] == 'array') {
                                $old = '[' . join(', ', $change[0]) . ']';
                                $new = '[' . join(', ', $change[1]) . ']';
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
}