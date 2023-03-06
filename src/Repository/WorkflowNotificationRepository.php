<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
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
            ->andWhere('w.transitionName = :transition')
            ->andWhere('w.workflowName = :workflow')
            ->setParameter('categories', $categories)
            ->setParameter('transition', $transition)
            ->setParameter('workflow', $workflow)
            ->getQuery()
            ->getResult();
    }
}
