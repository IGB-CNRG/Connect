<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\WorkflowNotification;
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

    public function findForTransition(string $workflow, string $transition, array $categories)
    {
        return $this->createQueryBuilder('w')
            ->join('w.memberCategories', 'm')
            ->andWhere('m in (:categories)')
            ->andWhere('w.stageName = :stage')
            ->andWhere('w.workflowName = :workflow')
            ->andWhere('w.event = :event')
            ->setParameter('categories', $categories)
            ->setParameter('stage', $transition)
            ->setParameter('workflow', $workflow)
            ->getQuery()
            ->getResult();
    }
}
