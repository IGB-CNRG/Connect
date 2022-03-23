<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\RoomKeyAffiliation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RoomKeyAffiliation|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoomKeyAffiliation|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoomKeyAffiliation[]    findAll()
 * @method RoomKeyAffiliation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomKeyAffiliationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomKeyAffiliation::class);
    }

    // /**
    //  * @return RoomKeyAffiliation[] Returns an array of RoomKeyAffiliation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RoomKeyAffiliation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
