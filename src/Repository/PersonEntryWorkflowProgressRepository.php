<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Workflow\PersonEntryWorkflowProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonEntryWorkflowProgress>
 *
 * @method PersonEntryWorkflowProgress|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonEntryWorkflowProgress|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonEntryWorkflowProgress[]    findAll()
 * @method PersonEntryWorkflowProgress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonEntryWorkflowProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonEntryWorkflowProgress::class);
    }

    /**
     * @return PersonEntryWorkflowProgress[] Returns an array of PersonEntryWorkflowProgress objects
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
