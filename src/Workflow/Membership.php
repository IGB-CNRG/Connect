<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow;

use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Entity\ThemeAffiliation;
use App\Log\ActivityLogger;
use App\Service\EntityManagerAware;
use App\Service\HistoricityManagerAware;
use App\Workflow\Approval\ApprovalStrategy;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class Membership implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;
    use HistoricityManagerAware;
    use EntityManagerAware;

    public const PLACE_NEED_ENTRY_FORM = 'need_entry_form';
    public const PLACE_ENTRY_FORM_SUBMITTED = 'entry_form_submitted';
    public const PLACE_NEED_CERTIFICATES = 'need_certificates';
    public const PLACE_CERTIFICATES_SUBMITTED = 'certificates_submitted';
    public const PLACE_ACTIVE = 'active';
    public const PLACE_EXIT_FORM_SUBMITTED = 'exit_form_submitted';
    public const PLACE_INACTIVE = 'inactive';

    public const TRANS_SUBMIT_ENTRY_FORM = 'submit_entry_form';
    public const TRANS_APPROVE_ENTRY_FORM = 'approve_entry_form';
    public const TRANS_RETURN_ENTRY_FORM = 'return_entry_form';
    public const TRANS_ACTIVATE_WITHOUT_CERTIFICATES = 'activate_without_certificates';
    public const TRANS_UPLOAD_CERTIFICATES = 'upload_certificates';
    public const TRANS_APPROVE_CERTIFICATES = 'approve_certificates';
    public const TRANS_RETURN_CERTIFICATES = 'return_certificates';
    public const TRANS_FORCE_ENTRY_FORM = 'force_entry_form';
    public const TRANS_SUBMIT_EXIT_FORM = 'submit_exit_form';
    public const TRANS_FORCE_EXIT_FORM = 'force_exit_form';
    public const TRANS_DEACTIVATE = 'deactivate';
    public const TRANS_REENTER = 'reenter';
    public const TRANS_REACTIVATE = 'reactivate';


    public const PLACES_NEEDING_APPROVAL = [
        self::PLACE_ENTRY_FORM_SUBMITTED,
        self::PLACE_CERTIFICATES_SUBMITTED,
        self::PLACE_EXIT_FORM_SUBMITTED,
    ];

    public const TRANSITIONS_NEEDING_APPROVAL = [
        self::TRANS_APPROVE_ENTRY_FORM,
        self::TRANS_RETURN_ENTRY_FORM,
        self::TRANS_APPROVE_CERTIFICATES,
        self::TRANS_RETURN_CERTIFICATES,
        self::TRANS_DEACTIVATE,
        self::TRANS_FORCE_ENTRY_FORM,
        self::TRANS_ACTIVATE_WITHOUT_CERTIFICATES,
    ];

    private readonly ServiceLocator $approvalLocator;

    public function __construct(
        private readonly WorkflowInterface $membershipStateMachine,
        #[TaggedLocator(ApprovalStrategy::class)] ServiceLocator $approvalLocator
    ) {
        $this->approvalLocator = $approvalLocator;
    }

    /**
     * Returns true if the given Person can be approved by the currently logged-in user.
     * @param Person $person
     * @return bool
     */
    public function canApprove(Person $person): bool
    {
        foreach (self::TRANSITIONS_NEEDING_APPROVAL as $transitionName) {
            if ($this->membershipStateMachine->can($person, $transitionName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the approvers for the given Person and workflow transition. If no transition is given, returns the
     * approvers for the Person's current workflow place.
     * @param Person $person
     * @param Transition|null $transition
     * @return Person[]|null
     */
    public function getApprovers(Person $person, ?Transition $transition = null): ?array
    {
        if ($approvalStrategy = $this->getApprovalStrategy($person, $transition)) {
            return $approvalStrategy->getApprovers($person);
        }

        return null;
    }

    /**
     * Returns the approval emails for the given Person and workflow transition. If no transition is given, returns the
     * approvers for the Person's current workflow place.
     * @param Person $person
     * @param Transition|null $transition
     * @return string[]
     */
    public function getApprovalEmails(Person $person, ?Transition $transition = null): array
    {
        if ($approvalStrategy = $this->getApprovalStrategy($person, $transition)) {
            return $approvalStrategy->getApprovalEmails($person);
        }

        return [];
    }

    /**
     * Returns the ApprovalStrategy for the given Person and workflow transition. If no transition is given, returns the
     * ApprovalStrategy for the Person's current workflow place.
     * @param Person $person
     * @param Transition|null $transition
     * @return ApprovalStrategy|null
     */
    public function getApprovalStrategy(Person $person, ?Transition $transition = null): ?ApprovalStrategy
    {
        $approvalStrategyClass = $this->membershipStateMachine->getMetadataStore()->getMetadata(
            'approvalStrategy',
            $transition ?? $this->getPlace($person)
        );
        if ($approvalStrategyClass
            && class_exists($approvalStrategyClass)
            && in_array(ApprovalStrategy::class, class_implements($approvalStrategyClass))) {
            /** @var ApprovalStrategy $approvalStrategy */
            return $this->approvalLocator->get($approvalStrategyClass);
        }

        return null;
    }

    /**
     * Returns the current workflow place for the given Person.
     * @param Person $person
     * @return string
     */
    public function getPlace(Person $person): string
    {
        return array_keys($this->membershipStateMachine->getMarking($person)->getPlaces())[0];
    }

    public function processEntry(Person $person, $trySilent = false): void
    {
        // Get the earliest start date from the new theme affiliations added
        $newAffiliations = $person->getThemeAffiliations()->filter(
            fn(ThemeAffiliation $themeAffiliation) => $themeAffiliation->getId() === null
        );
        $startDate = $this->historicityManager()->getEarliest($newAffiliations->toArray());

        // handle empty room affiliations. only update new ones.
        $newRooms = $person->getRoomAffiliations()->filter(
            fn(RoomAffiliation $affiliation) => $affiliation->getId() === null
        );
        foreach ($newRooms as $roomAffiliation) {
            $roomAffiliation->setStartedAt($startDate);
            if (!$roomAffiliation->getRoom()) {
                $person->removeRoomAffiliation($roomAffiliation);
            }
        }

        // handle empty supervisor/sponsor affiliations
        foreach ($newAffiliations as $themeAffiliation) {
            foreach ($themeAffiliation->getSponsorAffiliations() as $sponsorAffiliation) {
                $sponsorAffiliation->setStartedAt($themeAffiliation->getStartedAt())
                    ->setEndedAt($themeAffiliation->getEndedAt());
                if (!$sponsorAffiliation->getSponsor()) {
                    $themeAffiliation->removeSponsorAffiliation($sponsorAffiliation);
                }
            }
            foreach ($themeAffiliation->getSupervisorAffiliations() as $supervisorAffiliation) {
                $supervisorAffiliation->setStartedAt($themeAffiliation->getStartedAt())
                    ->setEndedAt($themeAffiliation->getEndedAt());
                if (!$supervisorAffiliation->getSupervisor()) {
                    $themeAffiliation->removeSupervisorAffiliation($supervisorAffiliation);
                }
            }
        }

        if (!$person->getUsername()) {
            $person->setUsername($person->getNetid());
        }
        $person->setMembershipUpdatedAt(new DateTimeImmutable());
        $this->entityManager()->persist($person);

        // if possible, reactivate. otherwise, if possible, force entry. otherwise, submit entry for approval.
        // todo when workflows are re-enabled, this needs to be updated with the REENTER transition for non-silent forms
        if ($this->membershipStateMachine->can($person, Membership::TRANS_REACTIVATE)) {
            $this->logger()->log($person, 'Reactivated');
            $this->membershipStateMachine->apply($person, Membership::TRANS_REACTIVATE);
        } elseif ($this->membershipStateMachine->can($person, Membership::TRANS_FORCE_ENTRY_FORM)
            && $trySilent) {
            $this->logger()->log($person, 'Silently submitted entry form', false);
            $this->membershipStateMachine->apply($person, Membership::TRANS_FORCE_ENTRY_FORM);
//        } else {
//            $logger->log($person, 'Submitted entry form');
//            $membershipStateMachine->apply($person, Membership::TRANS_SUBMIT_ENTRY_FORM);
        }
    }


    #[SubscribedService]
    private function logger(): ActivityLogger
    {
        return $this->container->get(__CLASS__.'::'.__FUNCTION__);
    }
}