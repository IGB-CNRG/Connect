<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Theme;
use App\Enum\ThemeRole;
use App\Service\HistoricityManagerAware;
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
    use ServiceSubscriberTrait, HistoricityManagerAware;

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
            ->select('p,ta,t,ra,r');
    }

    public function findCurrentForIndex()
    {
        $qb = $this->createIndexQueryBuilder()
            ->andWhere('ta is not null');
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
}
