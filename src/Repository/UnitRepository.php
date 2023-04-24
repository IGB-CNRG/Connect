<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Unit;
use App\Service\HistoricityManagerAware;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

use function Doctrine\ORM\QueryBuilder;

/**
 * @method Unit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Unit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Unit[]    findAll()
 * @method Unit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnitRepository extends ServiceEntityRepository implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, HistoricityManagerAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unit::class);
    }

    public function createFormSortedQueryBuilder()
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.parentUnit', 'c')
            ->addOrderBy('c.name')
            ->addOrderBy('d.name');
    }
    public function getFacultyAffiliatesDigest()
    {
        $nonFacultyQB = $this->_em->createQueryBuilder()
            ->select('identity(ta3.person)')
            ->from('App:ThemeAffiliation', 'ta3')
            ->andWhere('ta3.memberCategory=5'); // todo this is bad but will be made better when we have a report system

        $facultyQB = $this->_em->createQueryBuilder()
            ->select('count(p)')
            ->from('App:Person', 'p')
            ->leftJoin('p.themeAffiliations', 'ta')
            ->leftJoin('ta.memberCategory', 'm')
            ->leftJoin('p.unitAffiliations', 'da')
            ->andWhere('m.id=5')
            ->andWhere('da.unit=d');
        $this->historicityManager()->addCurrentConstraint($facultyQB, 'da');
        $this->historicityManager()->addCurrentConstraint($facultyQB, 'ta');

        $affiliateQB = $this->_em->createQueryBuilder()
            ->select('count(p2)')
            ->from('App:Person', 'p2')
            ->leftJoin('p2.themeAffiliations', 'ta2')
            ->leftJoin('ta2.memberCategory', 'm2')
            ->leftJoin('p2.unitAffiliations', 'da2')
            ->andWhere('m2.id=6')
            ->andWhere('da2.unit=d');
        $affiliateQB->andWhere($affiliateQB->expr()->notIn('p2', $nonFacultyQB->getDQL()));
        $this->historicityManager()->addCurrentConstraint($affiliateQB, 'da2');
        $this->historicityManager()->addCurrentConstraint($affiliateQB, 'ta2');

        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.parentUnit', 'c')
            ->select(
                'd.name as unit',
                'c.name as college',
                '(' . $facultyQB->getDQL() . ') as faculty',
                '(' . $affiliateQB->getDQL() . ') as affiliates'
            )
        ->orderBy('d.name');

        return $qb->getQuery()
            ->getResult();
    }
}
