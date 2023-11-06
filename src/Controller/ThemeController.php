<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\ThemeRole;
use App\Form\Theme\FilterType;
use App\Repository\PersonRepository;
use App\Repository\ThemeRepository;
use App\Repository\ThemeRoleRepository;
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
    public function view(Theme $theme, PersonRepository $personRepository, ThemeRoleRepository $roleRepository): Response
    {
        $people = $personRepository->findCurrentForTheme($theme);
        $themeRoles = array_map(fn(ThemeRole $role) => ['name'=>$role->getName(),'people'=>$personRepository->findByRoleInTheme($theme, $role)], $roleRepository->findAll());
        $filterForm = $this->createForm(FilterType::class);
        return $this->render('theme/view.html.twig', [
            'theme' => $theme,
            'people' => $people,
            'filterForm' => $filterForm->createView(),
            'themeRoles' => $themeRoles,
        ]);
    }
}
