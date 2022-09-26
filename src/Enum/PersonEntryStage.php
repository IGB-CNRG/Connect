<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum;

use App\Entity\Person;

enum PersonEntryStage: string implements WorkflowStage
{
    case SubmitEntryForm = 'submit_form';
    case ApproveEntryForm = 'approve_form';
    case UploadTrainingCertificates = 'upload_certs';
    case ApproveTrainingCertificates = 'approve_certs';

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
            self::SubmitEntryForm => self::ApproveEntryForm,
            self::ApproveEntryForm => self::UploadTrainingCertificates,
            self::UploadTrainingCertificates => self::ApproveTrainingCertificates,
            self::ApproveTrainingCertificates => null,
        };
    }

    public function previous(): self
    {
        return match($this){
            self::SubmitEntryForm => null,
            self::ApproveEntryForm => self::SubmitEntryForm,
            self::UploadTrainingCertificates => self::ApproveEntryForm,
            self::ApproveTrainingCertificates => self::UploadTrainingCertificates,
        };
    }
}
