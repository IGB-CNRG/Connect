<?php
/*
 * Copyright (c) 2026 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use App\Entity\Person;

class CertificateHelper
{
    const DRS_LAB_SAFETY = "DRS General Laboratory Safety";
    const DRS_BIOSAFETY = "DRS Understanding Biosafety";
    const DRS_BSL_2 = "DRS Safe Handling of Human Cell Lines";
    const IGB_LAB = "IGB Lab Workers";
    const IGB_STAFF = "IGB Support Staff";

    /**
     * @param Person $person
     * @return string[]
     */
    public function requiredCertificates(Person $person): array
    {
        if ($person->isOfficeWorkOnly()) {
            return [self::IGB_STAFF];
        } else {
            return [self::DRS_LAB_SAFETY, self::DRS_BIOSAFETY, self::IGB_LAB, self::DRS_BSL_2];
        }
    }
}