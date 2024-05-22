<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\ThemeRole;
use App\Form\Theme\FilterType;
use App\Repository\PersonRepository;
use App\Repository\ThemeRepository;
use App\Repository\ThemeRoleRepository;
use App\Service\HistoricityManager;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

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
    public function view(
        Theme $theme,
        HistoricityManager $historicityManager,
        PersonRepository $personRepository,
        ThemeRoleRepository $roleRepository,
        FormFactoryInterface $formFactory,
        Request $request,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] string $sort = 'name',
        #[MapQueryParameter] string $sortDirection = 'asc',
    ): Response {
        // Set defaults
        $pageSize = 10;
        $query = null;
        $themesToFilter = [$theme];
        $typesToFilter = [];
        $unitsToFilter = [];

        // No-name form gives us a cleaner query string, but stops handleRequest() from working correctly
        $filterForm = $formFactory->createNamed('', FilterType::class, null, ['method'=>'GET']);
        $filterForm->handleRequest($request);
        if ($filterForm->isSubmitted()) {
            // Pull parameters from form
            $pageSize = $filterForm->get('pageSize')->getData();
            $query = $filterForm->get('query')->getData();
            $typesToFilter = $filterForm->get('employeeType')->getData()->toArray();
            $unitsToFilter = $filterForm->get('unit')->getData()->toArray();
        }

        $qb = $personRepository->createIndexQueryBuilder();
        $historicityManager->addCurrentConstraint($qb, 'ta');
        $personRepository->addIndexFilters(
            $qb,
            $query,
            $sort,
            $sortDirection,
            $themesToFilter,
            $typesToFilter,
            [],
            $unitsToFilter
        );

        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($qb),
            $page,
            $pageSize
        );

        $themeRoles = array_map(
            fn(ThemeRole $role) => [
                'name' => $role->getName(),
                'people' => $personRepository->findByRoleInTheme($theme, $role),
            ],
            $roleRepository->findAll()
        );

        return $this->render('theme/view.html.twig', [
            'theme' => $theme,
            'people' => $pager,
            'filterForm' => $filterForm->createView(),
            'themeRoles' => $themeRoles,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'currentOnly' => true,
            'membersOnly' => false,
        ]);
    }
}
