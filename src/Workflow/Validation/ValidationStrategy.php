<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Validation;

use App\Entity\Workflow\WorkflowProgress;

interface ValidationStrategy
{
    /**
     * Validates the given workflow progress
     * @param WorkflowProgress $progress
     * @return bool True if valid
     */
    public function validate(WorkflowProgress $progress): bool;
}