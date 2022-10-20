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

    public function save(PersonEntryWorkflowProgress $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PersonEntryWorkflowProgress $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PersonEntryWorkflowProgress[] Returns an array of PersonEntryWorkflowProgress objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PersonEntryWorkflowProgress
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
