<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow;

use App\Entity\Person;
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
    ];

    public function __construct(private readonly WorkflowInterface $membershipStateMachine){}

    public function canApprove(Person $person): bool
    {
        foreach (self::TRANSITIONS_NEEDING_APPROVAL as $transitionName){
            if($this->membershipStateMachine->can($person, $transitionName)){
                return true;
            }
        }
        return false;
    }
}