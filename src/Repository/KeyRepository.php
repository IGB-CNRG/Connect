<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Key;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Key|null find($id, $lockMode = null, $lockVersion = null)
 * @method Key|null findOneBy(array $criteria, array $orderBy = null)
 * @method Key[]    findAll()
 * @method Key[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Key::class);
    }

    public function createFormQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('k')
            ->addOrderBy('k.name', 'ASC');
    }

}
