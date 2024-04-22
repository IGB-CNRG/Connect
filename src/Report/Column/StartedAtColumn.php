<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Report\Column;

use App\Entity\Person;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StartedAtColumn implements ReportColumnInterface
{
    /**
     * @param int $themeIndex Which of the person's themes to display, 1-indexed. This is useful if you want to display
     *     e.g. 3 themes.
     */
    public function __construct(private readonly int $themeIndex)
    {
    }

    public function getTitle(): string
    {
        return 'Start date '.$this->themeIndex;
    }

    public function getType(): string
    {
        return DataType::TYPE_NUMERIC;
    }

    public function getNumberFormat(): ?string
    {
        return 'mm/dd/yyyy';
    }

    public function getValue(Person $person)
    {
        if ($person->getThemeAffiliations()->get($this->themeIndex - 1)?->getStartedAt() !== null) {
            return Date::PHPToExcel($person->getThemeAffiliations()->get($this->themeIndex - 1)?->getStartedAt());
        }

        return null;
    }
}