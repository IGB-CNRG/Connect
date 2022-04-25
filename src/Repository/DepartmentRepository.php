<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Department|null find($id, $lockMode = null, $lockVersion = null)
 * @method Department|null findOneBy(array $criteria, array $orderBy = null)
 * @method Department[]    findAll()
 * @method Department[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function createFormSortedQueryBuilder()
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.college', 'c')
            ->addOrderBy('c.name')
            ->addOrderBy('d.name');
    }
}
