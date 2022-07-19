<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
//    #[Route('/', name: 'default')]
//    public function index(): Response
//    {
//        // TODO eventually add some kind of dashboard here
//        return $this->redirectToRoute('person');
////        return $this->render('default/index.html.twig', [
////            'controller_name' => 'DefaultController',
////        ]);
//    }

    #[Route('/copyright', name: 'copyright')]
    public function copyright()
    {
        return $this->render('default/copyright.html.twig');
    }
}
