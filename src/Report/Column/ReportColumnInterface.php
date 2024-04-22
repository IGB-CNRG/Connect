<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Report\Column;

use App\Entity\Person;

interface ReportColumnInterface
{
    public function getTitle(): string;
    public function getType(): string;
    public function getNumberFormat(): ?string;
    public function getValue(Person $person);
}