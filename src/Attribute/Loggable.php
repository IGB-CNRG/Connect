<?php

namespace App\Attribute;

use Attribute;

#[Attribute]
class Loggable
{
    public function __construct(public ?string $displayName = null, public bool $details = true, public string $type = 'text'){}
}