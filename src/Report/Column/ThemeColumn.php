<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Report\Column;

use App\Entity\Person;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ThemeColumn implements ReportColumnInterface
{
    /**
     * @param int $themeIndex Which of the person's themes to display, 1-indexed. This is useful if you want to display e.g. 3
     *     themes.
     */
    public function __construct(private readonly int $themeIndex)
    {
    }

    public function getTitle(): string
    {
        return 'Theme '.$this->themeIndex;
    }

    public function getType(): string
    {
        return DataType::TYPE_STRING;
    }

    public function getValue(Person $person)
    {
        return $person->getThemeAffiliations()->get($this->themeIndex-1)?->getTheme()->getShortName();
    }

    public function getNumberFormat(): ?string
    {
        return null;
    }
}