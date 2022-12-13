<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Entity\Person;
use App\Repository\PersonRepository;

class ReceptionApproval implements ApprovalStrategy
{
    public function __construct(private readonly PersonRepository $personRepository){}
    /**
     * @inheritDoc
     */
    public function getApprovers(Person $person): array
    {
        return $this->personRepository->findByRole('ROLE_KEY_MANAGER');
    }
}