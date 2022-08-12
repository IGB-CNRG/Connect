<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class HistoricityManager implements ServiceSubscriberInterface
{

    /**
     * @inheritDoc
     */
    public static function getSubscribedServices(): array
    {
        return [];
    }


    public function getCurrentEntities(Collection $collection): Collection
    {
        return $collection->filter(function ($entity) {
            return $entity->isCurrent();
        });
    }

    public function addCurrentConstraint(QueryBuilder $qb, $alias)
    {
        $qb->andWhere("$alias.endedAt is null or $alias.endedAt >= CURRENT_TIMESTAMP()")
            ->andWhere("$alias.startedAt is null or $alias.startedAt <= CURRENT_TIMESTAMP()");
    }
}