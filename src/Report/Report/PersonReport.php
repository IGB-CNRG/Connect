<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Report\Report;

use App\Entity\Person;
use App\Report\Column\EmailColumn;
use App\Report\Column\EndedAtColumn;
use App\Report\Column\FirstNameColumn;
use App\Report\Column\LastNameColumn;
use App\Report\Column\MemberCategoryColumn;
use App\Report\Column\NetidColumn;
use App\Report\Column\ReportColumnInterface;
use App\Report\Column\StartedAtColumn;
use App\Report\Column\StatusColumn;
use App\Report\Column\ThemeColumn;
use App\Report\Column\UinColumn;
use App\Report\Column\UnitColumn;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * PersonReport encapsulates a downloadable report of people from Connect. It keeps track of the people in the report
 * and the columns in the downloadable file. It also handles generating the file for download. A PersonReport can be
 * easily created using PersonReportBuilder.
 */
class PersonReport
{
    /**
     * @param Person[] $people
     * @param ReportColumnInterface[] $columns
     */
    public function __construct(private readonly array $people, private array $columns = [])
    {
    }

    public function getSpreadsheet(): Spreadsheet
    {
        if (count($this->columns) === 0) {
            $this->useDefaultColumns();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($this->columns as $columnIndex => $column) {
            $sheet->setCellValue([$columnIndex + 1, 1], $column->getTitle());
            $sheet->getStyle([$columnIndex + 1, 1])->getFont()->setBold(true);
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex + 1))->setAutoSize(true);
        }

        foreach ($this->people as $rowIndex => $person) {
            foreach ($this->columns as $columnIndex => $column) {
                // Indices are 1-indexed, and we need to skip the header row
                if ($column->getValue($person)) {
                    $sheet->setCellValueExplicit([$columnIndex + 1, $rowIndex + 2],
                        $column->getValue($person),
                        $column->getType());

                    if ($column->getNumberFormat()) {
                        $sheet->getStyle([$columnIndex + 1, $rowIndex + 2])->getNumberFormat()->setFormatCode(
                            $column->getNumberFormat()
                        );
                    }
                }
            }
        }

        return $spreadsheet;
    }

    public function useDefaultColumns(): void
    {
        $this->columns = [
            new LastNameColumn(),
            new FirstNameColumn(),
            new EmailColumn(),
            new UinColumn(),
            new NetidColumn(),
            new UnitColumn(),
            new StatusColumn(),
            new ThemeColumn(1),
            new MemberCategoryColumn(1),
            new StartedAtColumn(1),
            new EndedAtColumn(1),
            new ThemeColumn(2),
            new MemberCategoryColumn(2),
            new StartedAtColumn(2),
            new EndedAtColumn(2),
        ];
    }
}