<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Department;
use App\Service\HistoricityManagerAware;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

use function Doctrine\ORM\QueryBuilder;

/**
 * @method Department|null find($id, $lockMode = null, $lockVersion = null)
 * @method Department|null findOneBy(array $criteria, array $orderBy = null)
 * @method Department[]    findAll()
 * @method Department[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentRepository extends ServiceEntityRepository implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, HistoricityManagerAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function createFormSortedQueryBuilder()
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.college', 'c')
            ->addOrderBy('c.name')
            ->addOrderBy('d.name');
    }
    public function getFacultyAffiliatesDigest()
    {
        $nonFacultyQB = $this->_em->createQueryBuilder()
            ->select('identity(ta3.person)')
            ->from('App:ThemeAffiliation', 'ta3')
            ->andWhere('ta3.memberCategory=5');

        $facultyQB = $this->_em->createQueryBuilder()
            ->select('count(p)')
            ->from('App:Person', 'p')
            ->leftJoin('p.themeAffiliations', 'ta')
            ->leftJoin('ta.memberCategory', 'm')
            ->leftJoin('p.departmentAffiliations', 'da')
            ->andWhere('m.id=5')
            ->andWhere('da.department=d');
        $this->historicityManager()->addCurrentConstraint($facultyQB, 'da');
        $this->historicityManager()->addCurrentConstraint($facultyQB, 'ta');

        $affiliateQB = $this->_em->createQueryBuilder()
            ->select('count(p2)')
            ->from('App:Person', 'p2')
            ->leftJoin('p2.themeAffiliations', 'ta2')
            ->leftJoin('ta2.memberCategory', 'm2')
            ->leftJoin('p2.departmentAffiliations', 'da2')
            ->andWhere('m2.id=6')
            ->andWhere('da2.department=d');
        $affiliateQB->andWhere($affiliateQB->expr()->notIn('p2', $nonFacultyQB->getDQL()));
        $this->historicityManager()->addCurrentConstraint($affiliateQB, 'da2');
        $this->historicityManager()->addCurrentConstraint($affiliateQB, 'ta2');

        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.college', 'c')
            ->select(
                'd.name as department',
                'c.name as college',
                '(' . $facultyQB->getDQL() . ') as faculty',
                '(' . $affiliateQB->getDQL() . ') as affiliates'
            )
        ->orderBy('d.name');

        dump($qb->getDQL());
        dump($qb->getQuery()->getSQL());

        return $qb->getQuery()
            ->getResult();
    }
}
