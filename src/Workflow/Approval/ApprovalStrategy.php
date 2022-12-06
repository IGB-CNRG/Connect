<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Entity\Person;
use App\Entity\Workflow\WorkflowProgress;

interface ApprovalStrategy
{
    /**
     * @param WorkflowProgress $progress
     * @return Person[]
     */
    public function getApprovers(WorkflowProgress $progress): array;
}