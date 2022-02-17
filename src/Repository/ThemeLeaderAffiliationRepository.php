<?php

namespace App\Repository;

use App\Entity\ThemeLeaderAffiliation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ThemeLeaderAffiliation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThemeLeaderAffiliation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThemeLeaderAffiliation[]    findAll()
 * @method ThemeLeaderAffiliation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeLeaderAffiliationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThemeLeaderAffiliation::class);
    }

    // /**
    //  * @return ThemeLeaderAffiliation[] Returns an array of ThemeLeaderAffiliation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ThemeLeaderAffiliation
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
