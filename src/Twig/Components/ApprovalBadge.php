<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig\Components;

use App\Entity\Person;
use App\Repository\PersonRepository;
use App\Workflow\Membership;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ApprovalBadge
{
    public bool $small = false;
    public function __construct(private readonly Membership $membership, private readonly PersonRepository $personRepository)
    {
    }

    public function getPendingApprovalCount(){
        $peopleToApprove = $this->personRepository->findAllNeedingApproval();
        $myApprovals = array_filter($peopleToApprove, function (Person $person) {
            return $this->membership->canApprove($person);
        });
        return count($myApprovals);
    }
}
