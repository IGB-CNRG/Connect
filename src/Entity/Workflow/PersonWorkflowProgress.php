<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity\Workflow;

use App\Entity\Person;

interface PersonWorkflowProgress extends WorkflowProgress
{
    public function getPerson(): ?Person;

    public function setPerson(Person $person): self;
}