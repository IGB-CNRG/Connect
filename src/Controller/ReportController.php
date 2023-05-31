<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\ThemeAffiliation;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
                    if ($affiliation->getTheme()->getShortName() === 'CABBI') { // todo bad
                        return $carry;
                    }
                    if ($carry === 'faculty'
                        || $affiliation->getMemberCategory()->getName() === 'Faculty') { // todo bad
                        return 'faculty';
                    }
                    if ($carry === 'affiliate'
                        || $affiliation->getMemberCategory()->getName() === 'Affiliate') { // todo bad
                        return 'affiliate';
                    }

                    return null;
                }
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
}
