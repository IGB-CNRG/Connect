<?php

namespace App\Repository;

use App\Entity\MemberCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MemberCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberCategory[]    findAll()
 * @method MemberCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberCategory::class);
    }

    // /**
    //  * @return MemberType[] Returns an array of MemberType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MemberType
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
