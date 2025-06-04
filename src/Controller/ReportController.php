<?php
/*
 * Copyright (c) 2025 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\ThemeAffiliation;
use App\Report\Builder\PersonReportBuilder;
use App\Repository\PersonRepository;
use DateTime;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    #[Route('/report/unit-partners', name: 'report_unit_partners')]
    public function unitPartners(PersonRepository $personRepository): Response
    {
        $people = $personRepository->findCurrentForMembersOnlyIndex();
        $colleges = [];
        foreach ($people as $person) {
            // todo reduce the themeaffiliations (do we need to check if they're current?)
            $type = array_reduce(
                $person->getThemeAffiliations()->toArray(),
                function ($carry, ThemeAffiliation $affiliation) {
//                    if ($affiliation->getTheme()->getShortName() === 'CABBI') { // todo bad
//                        return $carry;
//                    }
                    if ($carry === 'faculty'
                        || $affiliation->getMemberCategory()->getName() === 'Faculty') { // todo bad
                        return 'faculty';
                    }
                    if ($carry === 'affiliate'
                        || $affiliation->getMemberCategory()->getName() === 'Affiliate') { // todo bad
                        return 'affiliate';
                    }

                    return null;
                },
            );
            if ($type !== null) {
                if ($person->getUnit()) {
                    $unit = $person->getUnit();
                    $unitName = $unit->getName();
                    $unitId = $unit->getId();
                    $college = $unit->getParentUnit();
                    $collegeName = $college ? $college->getName() : 'Other';
                } else {
                    $unitName = "Other";
                    $unitId = 0;
                    $collegeName = 'Other';
                }
                if (!key_exists($collegeName, $colleges)) {
                    $colleges[$collegeName] = [
                        'name' => $collegeName,
                        'units' => [],
                        'faculty' => 0,
                        'affiliates' => 0,
                    ];
                }

                if (!key_exists($unitName, $colleges[$collegeName]['units'])) {
                    $colleges[$collegeName]['units'][$unitName] = [
                        'unit' => $unitName,
                        'id' => $unitId,
                        'faculty' => 0,
                        'affiliates' => 0,
                        'people' => [],
                    ];
                }
                $colleges[$collegeName]['units'][$unitName]['people'][] = $person;
                if ($type === 'faculty') {
                    $colleges[$collegeName]['faculty']++;
                    $colleges[$collegeName]['units'][$unitName]['faculty']++;
                } elseif ($type === 'affiliate') {
                    $colleges[$collegeName]['affiliates']++;
                    $colleges[$collegeName]['units'][$unitName]['affiliates']++;
                }
            }
        }

        return $this->render('report/unit_partners.html.twig', [
            'colleges' => $colleges,
        ]);
    }

    #[Route('/report/people', name: 'report_people')]
    public function reportTest(
        PersonReportBuilder $reportBuilder,
        #[MapQueryParameter] string $query = '',
        #[MapQueryParameter] string $sort = 'name',
        #[MapQueryParameter] string $sortDirection = 'asc',
        #[MapQueryParameter] array $theme = [],
        #[MapQueryParameter] array $employeeType = [],
        #[MapQueryParameter] array $role = [],
        #[MapQueryParameter] array $unit = [],
        #[MapQueryParameter] bool $currentOnly = true,
        #[MapQueryParameter] bool $membersOnly = true,
    ): Response {
        $report = $reportBuilder
            ->setQuery($query)
            ->setSort($sort)
            ->setSortDirection($sortDirection)
            ->setThemesToFilter($theme)
            ->setTypesToFilter($employeeType)
            ->setRolesToFilter($role)
            ->setUnitsToFilter($unit)
            ->setCurrentOnly($currentOnly)
            ->setMembersOnly($membersOnly)
            ->getReport();

        // todo we could eventually pull the columns to render from some settings, or from more query params

        $writer = new Xlsx($report->getSpreadsheet());

        $filename = sprintf("Connect Report %s.xlsx", (new DateTime())->format('YmdHis'));
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename.'"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    #[Route('/mailing-list/everyone', name: 'mailing_list_everyone')]
    public function mailingListCSV(
        PersonReportBuilder $reportBuilder,
        #[MapQueryParameter] string $query = '',
        #[MapQueryParameter] string $sort = 'name',
        #[MapQueryParameter] string $sortDirection = 'asc',
        #[MapQueryParameter] array $theme = [],
        #[MapQueryParameter] array $employeeType = [],
        #[MapQueryParameter] array $role = [],
        #[MapQueryParameter] array $unit = [],
        #[MapQueryParameter] bool $currentOnly = true,
        #[MapQueryParameter] bool $membersOnly = false,
    ): Response {
        $report = $reportBuilder
            ->setQuery($query)
            ->setSort($sort)
            ->setSortDirection($sortDirection)
            ->setThemesToFilter($theme)
            ->setTypesToFilter($employeeType)
            ->setRolesToFilter($role)
            ->setUnitsToFilter($unit)
            ->setCurrentOnly($currentOnly)
            ->setMembersOnly($membersOnly)
            ->getReport();

        $list = $report->getMailingList();

        $response = new Response();
        $filename = 'EveryoneMailingList.csv';

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename.'"');
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Content-length', strlen($list));
        $response->setContent($list);

        return $response;
    }
}
