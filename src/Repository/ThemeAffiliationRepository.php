<?php

namespace App\Repository;

use App\Entity\ThemeAffiliation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ThemeAffiliation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThemeAffiliation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThemeAffiliation[]    findAll()
 * @method ThemeAffiliation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeAffiliationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThemeAffiliation::class);
    }

    // /**
    //  * @return ThemeAffiliation[] Returns an array of ThemeAffiliation objects
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
    public function findOneBySomeField($value): ?ThemeAffiliation
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
