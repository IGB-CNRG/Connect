<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Service\Attribute\SubscribedService;

trait SecurityAware
{
    #[SubscribedService]
    private function security(): Security
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}