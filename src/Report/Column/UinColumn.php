<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Report\Column;

use App\Entity\Person;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class UinColumn implements ReportColumnInterface
{

    public function getTitle(): string
    {
        return 'UIN';
    }

    public function getType(): string
    {
        return DataType::TYPE_STRING;
    }

    public function getValue(Person $person)
    {
        return $person->getUin()??'';
    }

    public function getNumberFormat(): ?string
    {
        return null;
    }
}