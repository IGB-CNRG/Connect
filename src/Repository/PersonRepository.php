<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Theme;
use App\Entity\ThemeRole;
use App\Service\HistoricityManagerAware;
use App\Workflow\Membership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository implements ServiceSubscriberInterface
{
    use HistoricityManagerAware;
    use ServiceSubscriberTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function createIndexQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('p')
            ->leftJoin('p.themeAffiliations', 'ta')
            ->leftJoin('ta.roles', 'tr')
            ->leftJoin('ta.memberCategory', 'mc')
            ->leftJoin('ta.theme', 't')
            ->leftJoin('t.parentTheme', 'pt')
            ->leftJoin(
                'p.roomAffiliations',
                'ra',
                'WITH',
                '(ra.endedAt is null or ra.endedAt>=CURRENT_TIMESTAMP()) and (ra.startedAt is null or ra.startedAt<=CURRENT_TIMESTAMP())',
            )
            ->leftJoin('ra.room', 'r')
            ->leftJoin('p.unit', 'u')
            ->select('p,ta,t,pt,ra,r,u,mc,tr');
    }

    private function addPagerParams(
        QueryBuilder $qb,
        ?string $sort,
        string $sortDirection,
    ) {
        if ($sort) {
            if ($sort === 'name') {
                $qb->orderBy('p.lastName', $sortDirection)
                    ->addOrderBy('p.firstName', $sortDirection);
            } elseif ($sort === 'unit') {
                $qb->orderBy('u.name', $sortDirection);
            } else {
                $qb->orderBy('p.'.$sort, $sortDirection);
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param bool $addedSecondJoin
     * @param array $filters
     * @param callable $queryCallback function(QueryBuilder $qb, $index, $value) Should return an array of queries to
     *     include and should set needed parameters on $qb.
     * @param callable $joinCallback
     * @param bool $currentOnly
     * @return bool
     */
    private function addFilter(
        QueryBuilder $qb,
        bool $addedSecondJoin,
        array $filters,
        callable $queryCallback,
        callable $joinCallback,
        bool $currentOnly
    ): bool {
        $queries = [];
        foreach ($filters as $i => $filter) {
            if ($filter) {
                $queries = array_merge($queries, $queryCallback($qb, $i, $filter));
            }
        }
        if (count($queries) > 0) {
            if (!$addedSecondJoin) {
                $qb->leftJoin('p.themeAffiliations', 'ta2');
                if ($currentOnly) {
                    $this->historicityManager()->addCurrentConstraint($qb, 'ta2');
                }
                $addedSecondJoin = true;
            }
            $joinCallback($qb);
            $qb->andWhere('('.join(' or ', $queries).')');
        }

        return $addedSecondJoin;
    }

    private function addThemeIdFilter(
        QueryBuilder $qb,
        bool $addedSecondJoin,
        array $themes,
        bool $currentOnly
    ): bool {
        return $this->addFilter(
            $qb,
            $addedSecondJoin,
            $themes,
            function ($qb2, $i, $id) {
                $qb2->setParameter("theme$i", $id);

                return ["t2.id = :theme$i", "parentTheme.id = :theme$i"];
            },
            function ($qb2) {
                $qb2->leftJoin('ta2.theme', 't2')
                    ->leftJoin('t2.parentTheme', 'parentTheme');
            },
            $currentOnly
        );
    }

    private function addThemeShortNameFilter(
        QueryBuilder $qb,
        bool $addedSecondJoin,
        array $themes,
        bool $currentOnly
    ): bool {
        return $this->addFilter(
            $qb,
            $addedSecondJoin,
            $themes,
            function ($qb2, $i, $id) {
                $qb2->setParameter("theme$i", $id);

                return ["t2.shortName = :theme$i", "parentTheme.shortName = :theme$i"];
            },
            function ($qb2) {
                $qb2->leftJoin('ta2.theme', 't2')
                    ->leftJoin('t2.parentTheme', 'parentTheme');
            },
            $currentOnly
        );
    }

    private function addMemberCategoryIdFilter(
        QueryBuilder $qb,
        bool $addedSecondJoin,
        array $memberCategories,
        bool $currentOnly
    ): bool {
        return $this->addFilter(
            $qb,
            $addedSecondJoin,
            $memberCategories,
            function ($qb2, $i, $id) {
                $qb2->setParameter("type$i", $id);

                return ["mc2.id = :type$i"];
            },
            function ($qb2) {
                $qb2->leftJoin('ta2.memberCategory', 'mc2');
            },
            $currentOnly
        );
    }

    private function addFriendlyMemberCategoryFilter(
        QueryBuilder $qb,
        bool $addedSecondJoin,
        array $memberCategories,
        bool $currentOnly
    ): bool {
        return $this->addFilter(
            $qb,
            $addedSecondJoin,
            $memberCategories,
            function ($qb2, $i, $id) {
                $qb2->setParameter("type$i", $id);

                return ["mc2.friendlyName = :type$i"];
            },
            function ($qb2) {
                $qb2->leftJoin('ta2.memberCategory', 'mc2');
            },
            $currentOnly
        );
    }

    private function addRoleIdFilter(
        QueryBuilder $qb,
        bool $addedSecondJoin,
        array $roleIds,
        bool $currentOnly
    ): bool {
        return $this->addFilter(
            $qb,
            $addedSecondJoin,
            $roleIds,
            function ($qb2, $i, $id) {
                $qb2->setParameter("role$i", $id);

                return ["tr2.id = :role$i"];
            },
            function ($qb2) {
                $qb2->leftJoin('ta2.roles', 'tr2');
            },
            $currentOnly
        );
    }

    private function addUnitIdFilter(
        QueryBuilder $qb,
        bool $addedSecondJoin,
        array $unitIds,
        bool $currentOnly
    ): bool {
        return $this->addFilter(
            $qb,
            $addedSecondJoin,
            $unitIds,
            function ($qb2, $i, $id) {
                $qb2->setParameter("unit$i", $id);

                return ["u.id = :unit$i"];
            },
            function ($qb2) {
            },
            $currentOnly
        );
    }

    public function createMembersOnlyIndexQueryBuilder(): QueryBuilder
    {
        return $this->createIndexQueryBuilder()
            ->leftJoin('t.themeType', 'tt')
            ->andWhere('ta is not null')
            ->andWhere('tt.isMember = true');
    }

    public function addIndexFilters(
        QueryBuilder $qb,
        ?string $query,
        ?string $sort = null,
        string $sortDirection = 'asc',
        array $themes = [],
        array $memberCategories = [],
        array $themeRoles = [],
        array $units = [],
        bool $currentOnly = true
    ): QueryBuilder {
        $this->addQuerySearch($qb, $query);
        $this->addPagerParams($qb, $sort, $sortDirection);

        $addedSecondJoin = $this->addThemeIdFilter($qb, false, $themes, $currentOnly);
        $addedSecondJoin = $this->addMemberCategoryIdFilter($qb, $addedSecondJoin, $memberCategories, $currentOnly);
        $addedSecondJoin = $this->addRoleIdFilter($qb, $addedSecondJoin, $themeRoles, $currentOnly);
        $this->addUnitIdFilter($qb, $addedSecondJoin, $units, $currentOnly);

        return $qb;
    }

    public function directoryQueryBuilder(
        ?string $query,
        ?string $sort = null,
        string $sortDirection = 'asc',
        array $themes = [],
        array $memberCategories = [],
        array $themeRoles = [],
        array $units = []
    ): QueryBuilder {
        $qb = $this->createIndexQueryBuilder()
            ->leftJoin('t.themeType', 'tt')
            ->andWhere('p.hideFromDirectory is null or p.hideFromDirectory = 0')
            ->andWhere('ta is not null')
            ->andWhere('tt.displayInDirectory = true');
        $this->historicityManager()->addCurrentConstraint($qb, 't');
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        $this->addQuerySearch($qb, $query);
        $this->addPagerParams($qb, $sort, $sortDirection);

        $addedSecondJoin = $this->addThemeShortNameFilter($qb, false, $themes, true);
        $addedSecondJoin = $this->addFriendlyMemberCategoryFilter($qb, $addedSecondJoin, $memberCategories, true);
        $addedSecondJoin = $this->addRoleIdFilter($qb, $addedSecondJoin, $themeRoles, true);
        $this->addUnitIdFilter($qb, $addedSecondJoin, $units, true);

        return $qb;
    }

    /**
     * @return Person[]
     */
    public function findCurrentForMembersOnlyIndex(): array
    {
        $qb = $this->createMembersOnlyIndexQueryBuilder();
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function findAllForMembersOnlyIndex(): array
    {
        return $this->createMembersOnlyIndexQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function findCurrentForIndex(): array
    {
        $qb = $this->createIndexQueryBuilder();
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function findAllForIndex(): array
    {
        return $this->createIndexQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function findAllNeedingApproval(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.membershipStatus in (:places)')
            ->orderBy('p.lastName')
            ->setParameter('places', Membership::PLACES_NEEDING_APPROVAL)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $role
     * @return Person[]
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere("p.roles like :role")
            ->addOrderBy('p.lastName')
            ->setParameter('role', "%$role%")
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Theme $theme
     * @param ThemeRole $role
     * @return Person[]
     */
    public function findByRoleInTheme(Theme $theme, ThemeRole $role): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.themeAffiliations', 'ta')
            ->leftJoin('ta.roles', 'r')
            ->andWhere("r = :role")
            ->andWhere('ta.theme = :theme')
            ->addOrderBy('p.lastName')
            ->setParameter('theme', $theme)
            ->setParameter('role', $role);
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @param Theme $theme
     * @return Person[]
     */
    public function findApproversInTheme(Theme $theme): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.themeAffiliations', 'ta')
            ->leftJoin('ta.roles', 'r')
            ->andWhere('ta.theme = :theme')
            ->andWhere('r.isApprover = 1')
            ->addOrderBy('p.lastName')
            ->setParameter('theme', $theme);
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb->getQuery()->getResult();
    }

    public function createDropdownQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.themeAffiliations', 'ta')
            ->andWhere('ta is not null')
            ->addOrderBy('p.lastName');
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb;
    }

    public function createSupervisorDropdownQueryBuilder(): QueryBuilder
    {
        return $this->createDropdownQueryBuilder()
            ->leftJoin('ta.memberCategory', 'mc')
            ->andWhere('mc.canSupervise = true');
    }

    public function createSortedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addOrderBy('p.lastName');
    }

    public function findByQuery(string $query)
    {
        $qb = $this->createSortedQueryBuilder();
        $this->addQuerySearch($qb, $query);

        return $qb->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

    private function addQuerySearch(QueryBuilder $builder, ?string $query): void
    {
        if ($query) {
            $queryWords = explode(' ', $query);
            foreach ($queryWords as $i => $word) {
                $builder->andWhere(
                    "p.firstName LIKE :query$i OR p.lastName LIKE :query$i OR p.preferredFirstName LIKE :query$i OR p.email LIKE :query$i OR p.username LIKE :query$i OR p.netid LIKE :query$i"
                )
                    ->setParameter("query$i", '%'.$word.'%');
            }
        }
    }
}
