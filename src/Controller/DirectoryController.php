<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Repository\MemberCategoryRepository;
use App\Repository\PersonRepository;
use App\Repository\ThemeRepository;
use App\Repository\ThemeRoleRepository;
use App\Repository\UnitRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class DirectoryController extends AbstractController
{
    #[Route('/directory', name: 'app_directory')]
    public function index(
        PersonRepository $personRepository,
        MemberCategoryRepository $memberCategoryRepository,
        ThemeRepository $themeRepository,
        ThemeRoleRepository $roleRepository,
        UnitRepository $unitRepository,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $pageSize = 10,
        #[MapQueryParameter] string $query = null,
        #[MapQueryParameter] string $sort = 'name',
        #[MapQueryParameter] string $sortDirection = 'asc',
        #[MapQueryParameter] array $theme = [],
        #[MapQueryParameter] array $type = [],
        #[MapQueryParameter] array $role = [],
        #[MapQueryParameter] array $unit = []
    ): Response {
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter(
                $personRepository->directoryQueryBuilder(
                    $query,
                    $sort,
                    $sortDirection,
                    $theme,
                    $type,
                    $role,
                    $unit
                )
            ),
            $page,
            $pageSize
        );
        $friendlyNames = $memberCategoryRepository->fetchAllFriendlyNames();
        $roles = $roleRepository->findAll();
        $themeGroups = $themeRepository->findDirectoryThemesGroupedByType();

        // Group units by
        $units = $unitRepository->findAllFormSorted();
        $unitGroups = [];
        $otherUnits = [];
        foreach ($units as $unitChoice) {
            if ($unitChoice->getChildUnits()->count() === 0) {
                if ($parentName = $unitChoice->getParentUnit()?->getName()) {
                    if (!key_exists($parentName, $unitGroups)) {
                        $unitGroups[$parentName] = [];
                    }

                    $unitGroups[$parentName][] = $unitChoice;
                } else {
                    $otherUnits[] = $unitChoice;
                }
            }
        }
        $unitGroups['Other'] = $otherUnits;

        return $this->render('directory/index.html.twig', [
            'people' => $pager,
            'memberCategories' => $friendlyNames,
            'themeGroups' => $themeGroups,
            'roles' => $roles,
            'unitGroups' => $unitGroups,
            'query' => $query,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'selectedTypes' => $type,
            'selectedThemes' => $theme,
            'selectedRoles' => $role,
            'selectedUnits' => $unit,
            'pageSize' => $pageSize,
        ]);
    }
}
