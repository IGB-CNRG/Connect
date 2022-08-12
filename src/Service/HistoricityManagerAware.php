<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use Symfony\Contracts\Service\Attribute\SubscribedService;

trait HistoricityManagerAware
{
    #[SubscribedService]
    private function historicityManager(): HistoricityManager
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}