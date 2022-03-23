<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Attribute;

use Attribute;

#[Attribute]
class Loggable
{
    public function __construct(public ?string $displayName = null, public bool $details = true, public string $type = 'text'){}
}