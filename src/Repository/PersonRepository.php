<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Theme;
use App\Enum\ThemeRole;
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
            ->leftJoin('ta.theme', 't')
            ->leftJoin('p.roomAffiliations', 'ra')
            ->leftJoin('ra.room', 'r')
            ->leftJoin('p.unitAffiliations', 'ua')
            ->leftJoin('ua.unit', 'u')
            ->select('p,ta,t,ra,r,ua,u');
    }

    public function createMembersOnlyIndexQueryBuilder(): QueryBuilder
    {
        return $this->createIndexQueryBuilder()
            ->andWhere('ta is not null')
            ->andWhere('t.isOutsideGroup = false');
    }


    public function findCurrentForMembersOnlyIndex()
    {
        $qb = $this->createMembersOnlyIndexQueryBuilder();
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb->getQuery()
            ->getResult();
    }

    public function findAllForMembersOnlyIndex()
    {
        return $this->createMembersOnlyIndexQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    public function findCurrentForIndex()
    {
        $qb = $this->createIndexQueryBuilder();
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb->getQuery()
            ->getResult();
    }

    public function findAllForIndex()
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
            ->andWhere("ta.themeRoles like :role")
            ->andWhere('ta.theme = :theme')
            ->addOrderBy('p.lastName')
            ->setParameter('theme', $theme)
            ->setParameter('role', "%$role->value%");
        $this->historicityManager()->addCurrentConstraint($qb, 'ta');

        return $qb->getQuery()
            ->getResult();
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
}
