<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Entity\Person;

interface ApprovalStrategy
{
    /**
     * @param Person $person
     * @return Person[]
     */
    public function getApprovers(Person $person): array;
}