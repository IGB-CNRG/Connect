<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Entity\MemberCategory;
use App\Entity\Person;
use App\Entity\ThemeAffiliation;
use App\Entity\UnitAffiliation;
use App\Repository\MemberCategoryRepository;
use App\Repository\PersonRepository;
use App\Repository\ThemeRepository;
use App\Repository\UnitRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-faculty-spreadsheet',
    description: 'Import faculty and affiliate information from the master list',
)]
class ImportFacultySpreadsheetCommand extends Command
{
    const AFFILIATE_COLUMNS = 5;
    const FACULTY_COLUMNS = 3;
    const FACULTY_COLUMN = 'U';
    const AFFILIATE_COLUMN = 'AD';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PersonRepository $personRepository,
        private readonly UnitRepository $unitRepository,
        private readonly ThemeRepository $themeRepository,
        private readonly MemberCategoryRepository $categoryRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The spreadsheet to import')
            ->addArgument('rows', InputArgument::REQUIRED, 'The maximum row to parse in the spreadsheet');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('file');
        $maxRow = $input->getArgument('rows');

        $io->note("Loading $filename...");

        $reader = new Xlsx();
        try {
            $spreadsheet = $reader->load($filename);
        } catch (Exception) {
            $io->error("Error loading given workbook");
            return Command::FAILURE;
        }

        try {
            $facultySheet = $spreadsheet->getSheet(0);
        } catch (Exception) {
            $io->error("No sheets in given workbook");
            return Command::FAILURE;
        }

        $facultyCategory = $this->categoryRepository->findOneBy(['name' => 'Faculty']);
        $affiliateCategory = $this->categoryRepository->findOneBy(['name' => 'Affiliate']);

        $io->note("Parsing people...");
        $found = 0;
        $new = 0;
        for ($row = 2; $row <= $maxRow; $row++) {
            $firstName = $facultySheet->getCell('E' . $row)->getValue();
            $lastName = $facultySheet->getCell('D' . $row)->getValue();
            $middleInitial = $facultySheet->getCell("F$row")->getValue();
            $isUofI = $facultySheet->getCell("G$row")->getStyle()->getFill() == Fill::FILL_NONE;
            $unitName = $facultySheet->getCell("P$row")->getValue();
            $email = $facultySheet->getCell("Q$row")->getValue();

            if (preg_match('/(.+)@illinois.edu/u', $email, $emailMatches)) {
                $netid = $emailMatches[1];
            } else {
                $netid = null;
            }

            $facThemes = $this->getThemes(
                self::FACULTY_COLUMN,
                self::FACULTY_COLUMNS,
                $row,
                $facultySheet,
                $facultyCategory
            );
            $affThemes = $this->getThemes(
                self::AFFILIATE_COLUMN,
                self::AFFILIATE_COLUMNS,
                $row,
                $facultySheet,
                $affiliateCategory
            );
            $themes = array_merge($facThemes, $affThemes);

            $person = $this->personRepository->findOneBy(['email' => $email]);
            if ($person === null) {
                $person = $this->personRepository->findOneBy(['firstName' => $firstName, 'lastName' => $lastName]);
            }
            if ($person === null) {
                if ($lastName === null) { // Skip blank rows
                    continue;
                }
                $person = new Person();
                $new++;
                // Now we can naively create the person
                $person->setFirstName($firstName)
                    ->setLastName($lastName)
                    ->setMiddleInitial($middleInitial)
                    ->setEmail($email)
                    ->setNetid($netid);
                if ($isUofI) {
                    $unitAffiliation = $this->getUnitAffiliation($unitName);
                    $person->addUnitAffiliation($unitAffiliation);
                }
                foreach ($themes as $themeInfo) {
                    $themeAffiliation = $this->getThemeAffiliation($themeInfo);
                    if ($themeAffiliation) {
                        $person->addThemeAffiliation($themeAffiliation);
                    }
                }
            } else {
                $found++;
                // We need to decide what needs to be updated and what needs to be created
                $person->setEmail($email); // Update the email to match the spreadsheet
                if ($netid !== null) {
                    $person->setNetid($netid); // Update the netid if we have one to update
                }

                // Add unit affiliation only if a similar one does not already exist
                if ($isUofI) {
                    $newUnitAffiliation = $this->getUnitAffiliation($unitName);
                    $foundOverlap = false;
                    foreach ($person->getUnitAffiliations() as $unitAffiliation) {
                        if ((($newUnitAffiliation->getUnit() !== null
                              && $newUnitAffiliation->getUnit() === $unitAffiliation->getUnit())
                             || ($newUnitAffiliation->getUnit() === null
                                 && $newUnitAffiliation->getOtherUnit()
                                    === $unitAffiliation->getOtherUnit()))
                            && $newUnitAffiliation->overlaps($unitAffiliation)) {
                            $foundOverlap = true;
                            break;
                        }
                    }
                    if (!$foundOverlap) {
                        $person->addUnitAffiliation($newUnitAffiliation);
                    }
                }

                // Update theme affiliations if they already exist, or create new ones
                foreach ($themes as $themeInfo) {
                    $themeAffiliation = $this->getThemeAffiliation($themeInfo);
                    if ($themeAffiliation) {
                        $foundMatch = false;
                        foreach ($person->getThemeAffiliations() as $existingAffiliation) {
                            if ($themeAffiliation->getTheme() === $existingAffiliation->getTheme()
                                && $themeAffiliation->getMemberCategory() === $existingAffiliation->getMemberCategory(
                                )) {
                                $foundMatch = true;
                                if ($existingAffiliation->getStartedAt() === null
                                    && $themeAffiliation->getStartedAt() !== null) {
                                    $existingAffiliation->setStartedAt($themeAffiliation->getStartedAt());
                                }
                                if ($existingAffiliation->getEndedAt() === null
                                    && $themeAffiliation->getEndedAt() !== null) {
                                    $existingAffiliation->setEndedAt($themeAffiliation->getEndedAt());
                                }
                            }
                        }
                        if (!$foundMatch) {
                            $person->addThemeAffiliation($themeAffiliation);
                        }
                    }
                }
            }
            $this->em->persist($person);
        }

        $this->em->flush();
        $io->success("Found $found existing people. Created $new new ones.");

        return Command::SUCCESS;
    }

    /**
     * @param $firstColumn
     * @param $numRecords
     * @param $row
     * @param $sheet
     * @param MemberCategory $category
     * @return array
     */
    protected function getThemes($firstColumn, $numRecords, $row, $sheet, MemberCategory $category): array
    {
        $themes = [];
        try {
            $firstColumnId = Coordinate::columnIndexFromString($firstColumn);
        } catch (Exception) {
            return $themes;
        }
        for ($i = $firstColumnId; $i < $firstColumnId + $numRecords * 3; $i += 3) {
            $theme = $sheet->getCell([$i, $row])->getValue();
            $start = $sheet->getCell([$i + 1, $row])->getValue();
            $end = $sheet->getCell([$i + 2, $row])->getValue();
            $this->getThemesFromRecord($theme, $start, $end, $category, $themes);
        }
        return $themes;
    }

    /**
     * @param $theme
     * @param $start
     * @param $end
     * @param MemberCategory $category
     * @param $themes
     * @return void
     */
    protected function getThemesFromRecord($theme, $start, $end, MemberCategory $category, &$themes): void
    {
        if ($theme === null) {
            return;
        }
        if (preg_match('/(.+)\/(.+)/u', $theme, $themeMatches)) { // Handle multiple themes in one column
            if (preg_match('/(.+)\/(.+)/u', $start, $startMatches)
                && preg_match('/(.+)\/(.+)/u', $end, $endMatches)) {
                // All three columns must have the slash to be valid
                $themes[] = [$themeMatches[1], $startMatches[1], $endMatches[1], $category];
                $themes[] = [$themeMatches[2], $startMatches[2], $endMatches[2], $category];
            }
        } else {
            if (!preg_match('/(.+)\/(.+)/u', $start)
                && !preg_match('/(.+)\/(.+)/u', $end)) {
                // No column must contain the slash to be valid
                $themes[] = [$theme, $start, $end, $category];
            }
        }
    }

    /**
     * @param mixed $unitName
     * @return UnitAffiliation
     */
    protected function getUnitAffiliation(mixed $unitName): UnitAffiliation
    {
        $unit = $this->unitRepository->findOneBy(['name' => $this->translateUnit($unitName)]);
        $unitAffiliation = new UnitAffiliation();
        if ($unit === null) {
            $unitAffiliation->setOtherUnit($unitName);
        } else {
            $unitAffiliation->setUnit($unit);
        }
        return $unitAffiliation;
    }

    /**
     * @param array $themeInfo
     * @return ThemeAffiliation|null
     */
    protected function getThemeAffiliation(array $themeInfo): ?ThemeAffiliation
    {
        [$name, $start, $end, $category] = $themeInfo;
        $name = trim($name);
        $theme = $this->themeRepository->findOneBy(['shortName' => $this->translateTheme($name)]);
        if ($theme === null) {
            return null;
        }
        $start = trim($start);
        if (preg_match('/(\\d\\d\\d\\d)/u', $start, $matches)) {
            $startDate = new DateTimeImmutable("$matches[1]-01-01");
        } else {
            $startDate = null;
        }
        $end = trim($end);
        if (preg_match('/(\\d\\d\\d\\d)/u', $end, $matches)) {
            $endDate = new DateTimeImmutable("$matches[1]-12-31");
        } else {
            $endDate = null;
        }
        return (new ThemeAffiliation())
            ->setTheme($theme)
            ->setStartedAt($startDate)
            ->setEndedAt($endDate)
            ->setMemberCategory($category);
    }

    private function translateTheme(string $name): string
    {
        return match ($name) {
            'CGHR' => 'CGRH',
            'MCELS' => 'M-CELS',
            'ONCPM' => 'ONC-PM',
            'GNPD' => 'GNDP',
            default => $name
        };
    }

    private function translateUnit(string $name): string
    {
        return match ($name) {
            'Natural Resources & Environmental Science' => 'Natural Resources & Environmental Sciences',
            'Molecular and Integrative Physiology' => 'Molecular & Integrative Physiology',
            'Molecular and Cellular Biology', 'Cell and Molecular Biology' => 'Molecular & Cellular Biology',
            'Mechanical Science and Engineering' => 'Mechanical Science & Engineering',
            'Materials Science and Engineering', 'Matrials Science and Engineering' => 'Materials Science & Engineering',
            'Kinesiology', 'Kinesiology and Community Health' => 'Kinesiology & Community Health',
            'Food Science & human Nutrition', 'Food Science and Human Nutrition' => 'Food Science & Human Nutrition',
            'Evolution, Ecology and Behavior' => 'Evolution, Ecology, & Behavior',
            'Electrical and Computer Engineering' => 'Electrical & Computer Engineering',
            'Crop Science' => 'Crop Sciences',
            'Comparative Biosciences' => 'Comparative Sciences',
            'Civil and Environmental Engineering' => '	Civil & Environmental Engineering',
            'Chemical and Biomolecular Engineering' => 'Chemical & Biomolecular Engineering',
            'Cell and Developmental Biology' => 'Cell & Developmental Biology',
            'Agricultural and Consumer Economics' => 'Agricultural & Consumer Economics',
            'Agricultural and Biological Engineering' => 'Agricultural & Biological Engineering',
            default => $name
        };
    }
}
