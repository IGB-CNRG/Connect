<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use App\Entity\Person;

class CertificateHelper
{
    const DRS_LAB_SAFETY = "DRS General Laboratory Safety";
    const DRS_BIOSAFETY = "DRS Understanding Biosafety";
    const IGB_LAB = "IGB Lab Workers";
    const IGB_STAFF = "IGB Support Staff";

    const URL = [
        self::DRS_LAB_SAFETY=>'',
        self::DRS_BIOSAFETY=>'',
        self::IGB_LAB=>'',
        self::IGB_STAFF=>'',
    ];

    /**
     * @param Person $person
     * @return string[]
     */
    public function requiredCertificates(Person $person): array
    {
        if ($person->isOfficeWorkOnly()) {
            return [self::IGB_STAFF];
        } else {
            return [self::DRS_LAB_SAFETY, self::DRS_BIOSAFETY, self::IGB_LAB];
        }
    }
}