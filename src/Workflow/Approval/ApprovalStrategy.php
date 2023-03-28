<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Entity\Person;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface ApprovalStrategy
{
    /**
     * @param Person $person
     * @return Person[]
     */
    public function getApprovers(Person $person): array;

    /**
     * @param Person $person
     * @return string[]
     */
    public function getApprovalEmails(Person $person): array;
}