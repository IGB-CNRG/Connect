<?php

namespace App\Repository;

use App\Entity\DepartmentAffiliation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DepartmentAffiliation|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepartmentAffiliation|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepartmentAffiliation[]    findAll()
 * @method DepartmentAffiliation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentAffiliationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepartmentAffiliation::class);
    }

    // /**
    //  * @return DepartmentAffiliation[] Returns an array of DepartmentAffiliation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DepartmentAffiliation
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
