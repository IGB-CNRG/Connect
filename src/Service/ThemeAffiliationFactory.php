<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use App\Entity\SponsorAffiliation;
use App\Entity\SupervisorAffiliation;
use App\Entity\ThemeAffiliation;

class ThemeAffiliationFactory
{
    public function new(): ThemeAffiliation
    {
        return (new ThemeAffiliation())
            ->addSponsorAffiliation(new SponsorAffiliation())
            ->addSupervisorAffiliation(new SupervisorAffiliation());
    }
}