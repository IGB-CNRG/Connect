<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum;

interface WorkflowStage
{
    public function canFinish($entity): bool;
    public function first(): self;
    public function next(): self;
    public function previous(): self;
}