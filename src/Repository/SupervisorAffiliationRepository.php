<?php

namespace App\Repository;

use App\Entity\SupervisorAffiliation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SupervisorAffiliation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupervisorAffiliation|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupervisorAffiliation[]    findAll()
 * @method SupervisorAffiliation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupervisorAffiliationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupervisorAffiliation::class);
    }

    // /**
    //  * @return SupervisorAffiliation[] Returns an array of SupervisorAffiliation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SupervisorAffiliation
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
