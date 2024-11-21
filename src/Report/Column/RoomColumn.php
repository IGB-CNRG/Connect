<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Report\Column;

use App\Entity\Person;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class RoomColumn implements ReportColumnInterface {
    /**
     * @param int $roomIndex Which of the person's rooms to display, 1-indexed. This is useful if you want to display e.g. 3
     *     rooms.
     */
    public function __construct(private readonly int $roomIndex) {}

    public function getTitle(): string
    {
        return 'Room '.$this->roomIndex;
    }

    public function getType(): string
    {
        return DataType::TYPE_STRING;
    }

    public function getNumberFormat(): ?string
    {
        return null;
    }

    public function getValue(Person $person)
    {
        return $person->getRoomAffiliations()->get($this->roomIndex-1)?->getRoom()->getNumber();
    }
}