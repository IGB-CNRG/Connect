<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum;

enum PreferredAddress: string
{
    case IGB = 'igb';
    case Campus = 'campus';
}