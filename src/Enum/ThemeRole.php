<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum;

enum ThemeRole: string {
    case ThemeLeader = 'theme_leader';
    case ThemeAdmin = 'theme_admin';
    case LabManager = 'lab_manager';

    public function getDisplayName(): string
    {
        return match ($this){
            self::ThemeLeader => 'Theme Leader',
            self::ThemeAdmin => 'Theme Admin',
            self::LabManager => 'Lab Manager',
        };
    }
}