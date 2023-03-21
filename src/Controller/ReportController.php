<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Repository\UnitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    #[Route('/report/unit-partners', name: 'report_unit_partners')]
    public function unitPartners(UnitRepository $unitRepository): Response
    {
        $units = $unitRepository->getFacultyAffiliatesDigest();
        $colleges = [];
        foreach($units as $unit){
            $college = $unit['college'] ?? 'Other';
            if(!key_exists($college, $colleges)){
                $colleges[$college] = [
                    'name' => $college,
                    'units' => [],
                    'faculty' => 0,
                    'affiliates' => 0,
                ];
            }
            $colleges[$college]['units'][] = $unit;
            $colleges[$college]['faculty'] += $unit['faculty'];
            $colleges[$college]['affiliates'] += $unit['affiliates'];
        }
        sort($colleges);
        return $this->render('report/unit_partners.html.twig', [
            'colleges' => $colleges,
        ]);
    }
}
