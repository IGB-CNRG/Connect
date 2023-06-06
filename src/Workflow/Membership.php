<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow;

use App\Entity\Person;
use App\Workflow\Approval\ApprovalStrategy;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

class Membership
{
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
        if($approvalStrategy = $this->getApprovalStrategy($person, $transition)){
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
}