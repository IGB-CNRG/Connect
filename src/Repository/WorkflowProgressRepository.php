<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Workflow\WorkflowProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkflowProgress>
 *
 * @method WorkflowProgress|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowProgress|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowProgress[]    findAll()
 * @method WorkflowProgress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkflowProgress::class);
    }

    /**
     * @return WorkflowProgress[] Returns an array of PersonEntryWorkflowProgress objects
     */
    public function findByApprover($person): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere(':person MEMBER OF p.approvers')
            ->setParameter('person', $person)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
