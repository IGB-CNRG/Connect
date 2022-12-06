<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WorkflowExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('workflow_completion', [WorkflowRuntime::class, 'completion']),
        ];
    }
}
