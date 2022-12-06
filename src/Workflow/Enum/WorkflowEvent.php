<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Enum;

enum WorkflowEvent: string
{
    case BeforeApproval = "before_approval";
    case AfterApproval = "after_approval";
}
