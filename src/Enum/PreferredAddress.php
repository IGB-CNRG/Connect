<?php

namespace App\Enum;

enum PreferredAddress: string
{
    case IGB = 'igb';
    case Home = 'home';
    case Campus = 'campus';
}