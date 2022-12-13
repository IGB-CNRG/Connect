<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Log;

use App\Entity\Log;

interface LogSubjectInterface
{
    public function addLog(Log $log): self;
}