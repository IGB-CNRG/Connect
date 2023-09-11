<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\DigestBuffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DigestBuffer>
 *
 * @method DigestBuffer|null find($id, $lockMode = null, $lockVersion = null)
 * @method DigestBuffer|null findOneBy(array $criteria, array $orderBy = null)
 * @method DigestBuffer[]    findAll()
 * @method DigestBuffer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DigestBufferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DigestBuffer::class);
    }

    public function save(DigestBuffer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DigestBuffer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
