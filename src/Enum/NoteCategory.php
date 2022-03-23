<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum;

enum NoteCategory: string
{
    case General = 'general';
    case IT = 'it';
    case Facilities = 'facilities';
    case Theme = 'theme';

    public function getLabel()
    {
        return match ($this) {
            self::General => 'General',
            self::IT => 'IT',
            self::Facilities => 'Facilities',
            self::Theme => 'Theme',
        };
    }
}