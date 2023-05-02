<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Log;

interface LoggableAffiliationInterface
{
    public function getSideA();
    public function getSideB();
    public function getAddLogMessageA():string;
    public function getUpdateLogMessageA():string;
    public function getRemoveLogMessageA():string;
    public function getAddLogMessageB():string;
    public function getUpdateLogMessageB():string;
    public function getRemoveLogMessageB():string;
}