<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum\Workflow;

trait ClearApproversTrait
{
    public function clearApprovers(): self
    {
        foreach ($this->getApprovers() as $approver){
            $this->removeApprover($approver);
        }
        return $this;
    }
}