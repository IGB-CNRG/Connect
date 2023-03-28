<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig\Runtime;

use App\Settings\SettingManager;
use Twig\Extension\RuntimeExtensionInterface;

class SettingExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly SettingManager $manager) {}

    public function getSetting(string $name): string
    {
        return $this->manager->get($name);
    }
}
