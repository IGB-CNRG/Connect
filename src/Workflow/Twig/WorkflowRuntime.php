<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Twig;

use App\Entity\Workflow\WorkflowProgress;
use App\Workflow\Entity\Factory\WorkflowFactory;
use Twig\Extension\RuntimeExtensionInterface;

class WorkflowRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly WorkflowFactory $workflowFactory){}

    public function completion(WorkflowProgress $workflowProgress): float
    {
        $stage = $workflowProgress->getStage($this->workflowFactory);

        if ($workflowProgress->isWaitingForApproval()) {
            return $stage->completionBeforeApproval();
        } else {
            return $stage->completionBeforeSubmission();
        }
    }
}