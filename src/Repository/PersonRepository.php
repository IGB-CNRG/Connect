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
        return $this->createQueryBuilder('p')
            ->leftJoin('p.themeAffiliations', 'ta')
            ->leftJoin('ta.roles', 'tr')
            ->leftJoin('ta.memberCategory', 'mc')
            ->leftJoin('ta.theme', 't')
            ->leftJoin('t.parentTheme', 'pt')
            ->leftJoin('p.roomAffiliations', 'ra')
            ->leftJoin('ra.room', 'r')
            ->leftJoin('p.unit', 'u')
            ->select('p,ta,t,pt,ra,r,u,mc,tr');
    }

    public function createMembersOnlyIndexQueryBuilder(): QueryBuilder
    {
        return $this->createIndexQueryBuilder()
            ->andWhere('ta is not null')
            ->andWhere('t.isOutsideGroup = false');
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
        $qb = $this->createMembersOnlyIndexQueryBuilder();
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');
        if ($query) {
            $qb->andWhere('p.firstName LIKE :query OR p.lastName LIKE :query')
                ->setParameter('query', '%'.$query.'%');
        }
        if ($sort) {
            if ($sort === 'name') {
                $qb->orderBy('p.lastName', $sortDirection)
                    ->addOrderBy('p.firstName', $sortDirection);
            } else {
                $qb->orderBy('p.'.$sort, $sortDirection);
            }
        }

        $addedSecondJoin = false;
        // Add theme query
        $queries = [];
        foreach ($themes as $i => $id) {
            if ($id) {
                $queries[] = "t2.shortName = :theme{$i}";
                $qb->setParameter("theme{$i}", $id);
            }
        }
        if (count($queries) > 0) {
            if(!$addedSecondJoin){
                $qb->leftJoin('p.themeAffiliations', 'ta2');
                $this->historicityManager()->addCurrentConstraint($qb, 'ta2');
                $addedSecondJoin = true;
            }
            $qb->leftJoin('ta2.theme', 't2')
                ->andWhere('('.join(' or ', $queries).')');
            $addedSecondJoin = true;
        }

        // Add member category query
        $queries = [];
        foreach ($memberCategories as $i => $id) {
            if ($id) {
                $queries[] = "mc2.friendlyName = :type{$i}";
                $qb->setParameter("type{$i}", $id);
            }
        }
        if (count($queries) > 0) {
            if(!$addedSecondJoin){
                $qb->leftJoin('p.themeAffiliations', 'ta2');
                $this->historicityManager()->addCurrentConstraint($qb, 'ta2');
                $addedSecondJoin = true;
            }
            $qb->leftJoin('ta2.memberCategory', 'mc2')
                ->andWhere('('.join(' or ', $queries).')');
        }

        // Add theme role query
        $queries = [];
        foreach ($themeRoles as $i => $id) {
            if ($id) {
                $queries[] = "tr2.id = :role{$i}";
                $qb->setParameter("role{$i}", $id);
            }
        }
        if (count($queries) > 0) {
            if(!$addedSecondJoin){
                $qb->leftJoin('p.themeAffiliations', 'ta2');
                $this->historicityManager()->addCurrentConstraint($qb, 'ta2');
                $addedSecondJoin = true;
            }
            $qb->leftJoin('ta2.roles', 'tr2')
                ->andWhere('('.join(' or ', $queries).')');
        }

        // Add unit query
        $queries = [];
        foreach ($units as $i => $id) {
            if ($id) {
                $queries[] = "u.id = :unit{$i}";
                $qb->setParameter("unit{$i}", $id);
            }
        }
        if (count($queries) > 0) {
            $qb->andWhere('('.join(' or ', $queries).')');
        }

        return $qb;
    }

    /**
     * @return Person[]
     */
    public function findCurrentForMembersOnlyIndex()
    {
        $qb = $this->createMembersOnlyIndexQueryBuilder();
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function findAllForMembersOnlyIndex()
    {
        return $this->createMembersOnlyIndexQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function findCurrentForIndex()
    {
        $qb = $this->createIndexQueryBuilder();
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function findAllForIndex()
    {
        return $this->createIndexQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Theme $theme
     * @return Person[]
     */
    public function findCurrentForTheme(Theme $theme): array
    {
        $qb = $this->createIndexQueryBuilder()
            ->leftJoin('p.themeAffiliations', 'ta2')
            ->leftJoin('ta2.theme', 't2')
            ->andWhere('(ta2.theme = :theme or t2.parentTheme = :theme)')
            ->setParameter('theme', $theme);
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');
        $this->historicityManager()->addCurrentConstraint($qb, 'ta2');

        return $qb->getQuery()
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

    public function createDropdownQueryBuilder()
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.themeAffiliations', 'ta')
            ->andWhere('ta is not null')
            ->addOrderBy('p.lastName');
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb;
    }

    public function createSupervisorDropdownQueryBuilder()
    {
        return $this->createDropdownQueryBuilder()
            ->leftJoin('ta.memberCategory', 'mc')
            ->andWhere('mc.canSupervise = true');
    }

    public function createSortedQueryBuilder()
    {
        return $this->createQueryBuilder('p')
            ->addOrderBy('p.lastName');
    }
}
