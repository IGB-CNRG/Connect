<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Validation;

use App\Entity\Workflow\WorkflowProgress;

/**
 * A validation strategy that performs no validation!
 */
class EmptyValidation implements ValidationStrategy
{

    public function __construct() {}

    /**
     * @inheritDoc
     */
    public function validate(WorkflowProgress $progress): bool
    {
        return true;
    }
}