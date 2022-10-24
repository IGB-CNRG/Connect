<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum\Workflow;

use App\Entity\Person;

enum PersonEntryStage: string implements WorkflowStage
{
    case SubmitEntryForm = 'submit_form';
    case UploadTrainingCertificates = 'upload_certs';

    /**
     * @param Person $entity
     * @return bool
     */
    public function canFinish($entity): bool
    {
        // todo check whether the given person is ready to finish this stage
        return false;
    }

    public function first(): self
    {
        return self::SubmitEntryForm;
    }

    public function next(): self
    {
        return match ($this) {
            self::SubmitEntryForm => self::UploadTrainingCertificates,
            self::UploadTrainingCertificates => null,
        };
    }

    public function previous(): self
    {
        return match($this){
            self::SubmitEntryForm => null,
            self::UploadTrainingCertificates => self::SubmitEntryForm,
        };
    }

    public function position(): int
    {
        return match($this){
            self::SubmitEntryForm => 0,
            self::UploadTrainingCertificates => 1,
        };
    }

    public function message()
    {
        return "person_entry.message.".$this->value;
    }

    public function approvalMessage()
    {
        return "person_entry.approval_message.".$this->value;
    }

    public function approvers(): WorkflowApproval
    {
        return match($this){
            self::SubmitEntryForm => WorkflowApproval::ThemeApproval,
            self::UploadTrainingCertificates => WorkflowApproval::ReceptionApproval,
        };
    }
}
