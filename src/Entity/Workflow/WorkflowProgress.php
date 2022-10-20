<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity\Workflow;

use App\Entity\Person;
use App\Enum\Workflow\WorkflowStage;
use Doctrine\Common\Collections\Collection;

interface WorkflowProgress
{
    public function getStage(): ?WorkflowStage;

    public function setStage(?WorkflowStage $stage): self;

    /**
     * @return Collection<int, Person>
     */
    public function getApprovers(): Collection;

    public function addApprover(Person $approver): self;

    public function removeApprover(Person $approver): self;

    public function clearApprovers(): self;
}