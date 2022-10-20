<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum\Workflow;

enum WorkflowApproval
{
    case ThemeApproval;
    case ReceptionApproval; //todo does this need to be renamed to be more generalized?
}
