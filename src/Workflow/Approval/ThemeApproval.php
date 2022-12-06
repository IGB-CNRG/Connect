<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Entity\Workflow\WorkflowProgress;
use App\Enum\ThemeRole;
use App\Repository\PersonRepository;

class ThemeApproval implements ApprovalStrategy
{
    public function __construct(private readonly PersonRepository $personRepository){}
    /**
     * @inheritDoc
     */
    public function getApprovers(WorkflowProgress $progress): array
    {
        $theme = $progress->getPerson()->getThemeAffiliations()[0]->getTheme(
        ); // todo this is a little naive, but works for entries
        $admins = $this->personRepository->findByRoleInTheme($theme, ThemeRole::ThemeAdmin);
        $managers = $this->personRepository->findByRoleInTheme($theme, ThemeRole::LabManager);
        return array_merge($admins, $managers); // todo fall back to somebody if there are no theme approvers
    }


}