<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Twig\Runtime;

use App\Entity\Person;
use App\Workflow\Membership;
use Twig\Extension\RuntimeExtensionInterface;

class WorkflowRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly Membership $membership){

    }

    /**
     * @param Person $person
     * @return Person[]
     */
    public function getMembershipApprovers(Person $person): array
    {
        return $this->membership->getApprovers($person);
    }
}