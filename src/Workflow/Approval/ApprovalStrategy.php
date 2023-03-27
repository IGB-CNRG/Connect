<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Entity\Person;
use App\Repository\PersonRepository;
use App\Service\HistoricityManager;

interface ApprovalStrategy
{
    public function __construct(
        PersonRepository $personRepository,
        HistoricityManager $historicityManager
    );

    /**
     * @param Person $person
     * @return Person[]
     */
    public function getApprovers(Person $person): array;

    /**
     * @param Person $person
     * @return string[]
     */
    public function getApprovalEmails(Person $person): array;
}