<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;

trait EntityManagerAware
{
    #[SubscribedService]
    private function entityManager(): EntityManagerInterface
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}