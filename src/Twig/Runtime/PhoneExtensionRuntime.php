<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig\Runtime;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Twig\Extension\RuntimeExtensionInterface;

class PhoneExtensionRuntime implements RuntimeExtensionInterface
{
    private readonly PhoneNumberUtil $phoneNumberUtil;
    public function __construct()
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    public function formatPhoneNumber(?string $numberStr): string
    {
        if(!$numberStr){
            return '';
        }
        $numberProto = $this->phoneNumberUtil->parse($numberStr, 'US');
        return $this->phoneNumberUtil->format($numberProto, PhoneNumberFormat::NATIONAL);
    }
}
