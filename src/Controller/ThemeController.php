<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\Theme;
use App\Repository\PersonRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThemeController extends AbstractController
{
    #[Route('/theme', name: 'theme')]
    public function index(ThemeRepository $repository): Response
    {
        $themes = $repository->findCurrentThemes();
        $nonResearchThemes = $repository->findCurrentNonResearchThemes();
        $outsideGroups = $repository->findCurrentOutsideGroups();
        return $this->render('theme/index.html.twig', [
            'themes' => $themes,
            'nonResearchThemes' => $nonResearchThemes,
            'outsideGroups' => $outsideGroups,
        ]);
    }

    #[Route('/theme/{shortName}', name: 'theme_view')]
    public function view(Theme $theme, PersonRepository $personRepository): Response
    {
        return $this->render('theme/view.html.twig', [
            'theme' => $theme,
        ]);
    }
}
