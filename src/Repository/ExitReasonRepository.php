<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\ExitReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExitReason>
 *
 * @method ExitReason|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExitReason|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExitReason[]    findAll()
 * @method ExitReason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExitReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExitReason::class);
    }

//    /**
//     * @return ExitReason[] Returns an array of ExitReason objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ExitReason
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
