<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use App\Entity\HistoricalEntityInterface;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;

class HistoricityManager
{
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

    /**
     * @param HistoricalEntityInterface[] $affiliations
     * @param DateTimeInterface $endDate
     * @param string $exitReason
     * @return void
     */
    public function endAffiliations(array $affiliations, DateTimeInterface $endDate, string $exitReason): void
    {
        foreach ($affiliations as $affiliation) {
            if ($affiliation->wasCurrentAtDate($endDate)) {
                $affiliation->setEndedAt($endDate);
                if (method_exists($affiliation, 'setExitReason')) {
                    $affiliation->setExitReason($exitReason);
                }
            }
        }
    }
}