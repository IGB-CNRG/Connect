<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
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

    public function findCurrentNonResearchThemes()
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.isNonResearch=true')
            ->andWhere('t.isOutsideGroup=false')
            ->addOrderBy('t.shortName');
        $this->historicityManager()->addCurrentConstraint($qb, 't');
        return $qb->getQuery()
            ->getResult();
    }

    public function findCurrentOutsideGroups()
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.isNonResearch=false')
            ->andWhere('t.isOutsideGroup=true')
            ->addOrderBy('t.shortName');
        $this->historicityManager()->addCurrentConstraint($qb, 't');
        return $qb->getQuery()
            ->getResult();
    }

    public function findCurrentThemes()
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.isNonResearch=false')
            ->andWhere('t.isOutsideGroup=false')
            ->addOrderBy('t.shortName');
        $this->historicityManager()->addCurrentConstraint($qb, 't');
        return $qb->getQuery()
            ->getResult();
    }

}
