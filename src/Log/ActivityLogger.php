<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Log;

use App\Entity\Log;
use App\Entity\Person;
use App\Service\EntityManagerAware;
use App\Service\SecurityAware;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ActivityLogger implements ServiceSubscriberInterface
{
    use EntityManagerAware;
    use SecurityAware;
    use ServiceSubscriberTrait;

    private const DATE_FORMAT = 'n/j/Y';

    public function log(LogSubjectInterface $subject, ?string $message, bool $addContext = true): void
    {
        if ($message) {
            $context = null;
            if ($addContext && $subject instanceof Person) {
                $context = $this->normalizePersonContext(
                    $subject
                ); // todo later add a method to LogSubjectInterface that returns the context group
            }
            $log = (new Log())
                ->setText($message)
                ->setUser($this->security()->getUser())
                ->setContext($context);
            $subject->addLog($log);
            $this->entityManager()->persist($log);
        }
    }

    public function logPersonEdit(Person $person): void
    {
        $this->log($person, $this->getEntityEditMessage($person));
        $uow = $this->entityManager()->getUnitOfWork();
        $uow->computeChangeSets();

        $reflection = new ReflectionClass($person::class);
        foreach ($reflection->getProperties() as $property) {
            $propertyReflection = new ReflectionProperty($person::class, $property->name);
            $attributes = $propertyReflection->getAttributes(LoggableManyRelation::class);
            if (count($attributes) > 0) {
                $this->logAffiliationChanges($this->propertyAccessor()->getValue($person, $property->name));
            }
        }
    }

    public function logNewAffiliation(LoggableAffiliationInterface $affiliation): void
    {
        $this->log($affiliation->getSideA(), $affiliation->getAddLogMessageA());
        $this->log($affiliation->getSideB(), $affiliation->getAddLogMessageB());
    }

    public function logUpdatedAffiliation(LoggableAffiliationInterface $affiliation): void
    {
        $this->log(
            $affiliation->getSideA(),
            $this->getEntityEditMessage($affiliation, $affiliation->getUpdateLogMessageA())
        );
        $this->log(
            $affiliation->getSideB(),
            $this->getEntityEditMessage($affiliation, $affiliation->getUpdateLogMessageB())
        );
    }

    public function logRemovedAffiliation(LoggableAffiliationInterface $affiliation): void
    {
        $this->log($affiliation->getSideA(), $affiliation->getRemoveLogMessageA());
        $this->log($affiliation->getSideB(), $affiliation->getRemoveLogMessageB());
    }

    /* Helpers */

    /**
     * @param PersistentCollection $affiliations
     * @return void
     */
    private function logAffiliationChanges(Collection $affiliations): void
    {
        if ($affiliations->isDirty()) {
            $inserted = $affiliations->getInsertDiff();
            /** @var LoggableAffiliationInterface $affiliation */
            foreach ($inserted as $affiliation) {
                $this->logNewAffiliation($affiliation);
            }
            $deleted = $affiliations->getDeleteDiff();
            foreach ($deleted as $affiliation) {
                $this->logRemovedAffiliation($affiliation);
            }
        }
        foreach ($affiliations as $affiliation) {
            $this->logUpdatedAffiliation($affiliation);
        }
    }

    private function normalizePersonContext(Person $person): string
    {
        $normalizerContext = (new ObjectNormalizerContextBuilder())
            ->withGroups('log:person')
            ->withCircularReferenceHandler(function ($object) {
                return $object->__toString();
            })
            ->toArray();
        $normalizerContext['iri'] = false;

        return $this->serializer()->serialize($person, 'json', $normalizerContext);
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
                                    $new = $change[1]->format(self::DATE_FORMAT);
                                } elseif (array_key_exists('type', $loggableArguments)
                                    && $loggableArguments['type'] == 'array') {
                                    $new = '['.join(', ', $change[1]).']';
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
                    } elseif (!array_key_exists('details', $loggableArguments)
                        || $loggableArguments['details'] === true) {
                        // Changed
                        if (array_key_exists('type', $loggableArguments) && $loggableArguments['type'] == 'date') {
                            $old = $change[0]->format(self::DATE_FORMAT);
                            $new = $change[1]->format(self::DATE_FORMAT);
                        } elseif (array_key_exists('type', $loggableArguments)
                            && $loggableArguments['type'] == 'array') {
                            $old = '['.join(', ', $change[0]).']';
                            $new = '['.join(', ', $change[1]).']';
                        } else {
                            $old = $change[0];
                            $new = $change[1];
                        }
                        $changes[] = sprintf("changed %s from '%s' to '%s'", $fieldName, $old, $new);
                    } else {
                        $changes[] = sprintf("changed %s", $fieldName);
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

    #[SubscribedService]
    function serializer(): SerializerInterface
    {
        return $this->container->get(__CLASS__.'::'.__FUNCTION__);
    }

    #[SubscribedService]
    function propertyAccessor(): PropertyAccessorInterface
    {
        return $this->container->get(__CLASS__.'::'.__FUNCTION__);
    }
}