<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig\Extension;

use App\Twig\Runtime\SettingExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SettingExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_setting', [SettingExtensionRuntime::class, 'getSetting']),
        ];
    }
}
