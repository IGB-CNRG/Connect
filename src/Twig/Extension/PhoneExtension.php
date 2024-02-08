<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig\Extension;

use App\Twig\Runtime\PhoneExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PhoneExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('phone_number', [PhoneExtensionRuntime::class, 'formatPhoneNumber']),
        ];
    }
}
