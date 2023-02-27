<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Log;

use Attribute;

#[Attribute]
class LoggableManyRelation
{
    public function __construct(public ?string $displayName = null){}
}