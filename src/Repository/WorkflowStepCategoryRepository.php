<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\WorkflowStepCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WorkflowStepCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowStepCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowStepCategory[]    findAll()
 * @method WorkflowStepCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowStepCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkflowStepCategory::class);
    }

    // /**
    //  * @return WorkflowStepCategory[] Returns an array of WorkflowStepCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WorkflowStepCategory
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
