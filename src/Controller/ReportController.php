<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Repository\CollegeRepository;
use App\Repository\DepartmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    #[Route('/report/unit-partners', name: 'report_unit_partners')]
    public function unitPartners(CollegeRepository $collegeRepository, DepartmentRepository $departmentRepository): Response
    {
        $departments = $departmentRepository->getFacultyAffiliatesDigest();
        $colleges = [];
        foreach($departments as $department){
            $college = $department['college'] ?? 'Other';
            if(!key_exists($college, $colleges)){
                $colleges[$college] = [
                    'name' => $college,
                    'departments' => [],
                    'faculty' => 0,
                    'affiliates' => 0,
                ];
            }
            $colleges[$college]['departments'][] = $department;
            $colleges[$college]['faculty'] += $department['faculty'];
            $colleges[$college]['affiliates'] += $department['affiliates'];
        }
        sort($colleges);
        dump($colleges);
        return $this->render('report/unit_partners.html.twig', [
            'colleges' => $colleges,
        ]);
    }
}
