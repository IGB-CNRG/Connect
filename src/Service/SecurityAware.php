<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\SubscribedService;

trait SecurityAware
{
    #[SubscribedService]
    private function security(): Security
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}