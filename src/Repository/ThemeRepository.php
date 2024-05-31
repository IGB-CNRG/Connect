<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Theme;
use App\Service\HistoricityManagerAware;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

/**
 * @method Theme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Theme|null findOneBy(array $criteria, array $orderBy = null)
 * @method Theme[]    findAll()
 * @method Theme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeRepository extends ServiceEntityRepository implements ServiceSubscriberInterface
{
    use HistoricityManagerAware;
    use ServiceSubscriberTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    public function createFormSortedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.shortName');
    }

    public function createCurrentFormSortedQueryBuilder(): QueryBuilder
    {
        $qb = $this->createFormSortedQueryBuilder();
        $this->historicityManager()->addCurrentConstraint($qb, 't');
        return $qb;
    }

    /**
     * @return Theme[]
     */
    public function findCurrentThemes(): array
    {
        $qb = $this->createQueryBuilder('t')
            ->addOrderBy('t.shortName');
        $this->historicityManager()->addCurrentConstraint($qb, 't');
        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @return Theme[][]
     */
    public function findCurrentThemesGroupedByType(): array
    {
        $themes = $this->findCurrentThemes();

        return $this->getThemeGroups($themes);
    }

    /**
     * @return Theme[][]
     */
    public function findDirectoryThemesGroupedByType(): array
    {
        $themes = $this->findThemesToDisplayInDirectory();

        return $this->getThemeGroups($themes);
    }

    /**
     * @return Theme[]
     */
    public function findThemesToDisplayInDirectory(): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.themeType', 'tt')
            ->andWhere('tt.displayInDirectory=true')
            ->addOrderBy('tt.id')
            ->addOrderBy('t.shortName');
        $this->historicityManager()->addCurrentConstraint($qb, 't');
        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @param Theme[] $themes
     * @return Theme[][]
     */
    protected function getThemeGroups(array $themes): array
    {
        $themeGroups = [];
        foreach ($themes as $themeToGroup) {
            $group = $themeToGroup->getThemeType()->getName();

            if (!key_exists($group, $themeGroups)) {
                $themeGroups[$group] = [];
            }
            $themeGroups[$group][] = $themeToGroup;
        }

        return $themeGroups;
    }
}
