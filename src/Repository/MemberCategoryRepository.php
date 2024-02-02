<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Repository;

use App\Entity\MemberCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MemberCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberCategory[]    findAll()
 * @method MemberCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberCategory::class);
    }

    public function createFormSortedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('mc')
            ->addOrderBy('mc.name');
    }

    /**
     * Fetches all unique friendly names from the MemberCategory entity
     *
     * @return string[] An array of unique friendly names
     */
    public function fetchAllFriendlyNames(): array
    {
        return $this->createQueryBuilder('mc')
            ->select('distinct mc.friendlyName')
            ->addOrderBy('mc.friendlyName')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
