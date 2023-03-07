<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Entity\Person;
use App\Repository\PersonRepository;

interface ApprovalStrategy
{
    public function __construct(PersonRepository $personRepository);
    /**
     * @param Person $person
     * @return Person[]
     */
    public function getApprovers(Person $person): array;
}