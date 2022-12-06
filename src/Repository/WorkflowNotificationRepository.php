<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\MemberCategory;
use App\Entity\Workflow\WorkflowNotification;
use App\Workflow\Entity\Stage;
use App\Workflow\Enum\WorkflowEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkflowNotification>
 *
 * @method WorkflowNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowNotification[]    findAll()
 * @method WorkflowNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkflowNotification::class);
    }

//    /**
//     * @return WorkflowNotification[] Returns an array of WorkflowNotification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function findByStage(Stage $stage, MemberCategory $category, WorkflowEvent $event)
    {
        return $this->createQueryBuilder('w')
            ->join('w.memberCategories', 'm')
            ->andWhere('m.id = :categoryId')
            ->andWhere('w.stageName = :stage')
            ->andWhere('w.workflowName = :workflow')
            ->andWhere('w.event = :event')
            ->setParameter('categoryId', $category->getId())
            ->setParameter('stage', $stage->getName())
            ->setParameter('workflow', $stage->getWorkflow()->getName())
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult();
    }

//    public function findOneBySomeField($value): ?WorkflowNotification
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
