<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use Doctrine\Common\Collections\Collection;
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
}