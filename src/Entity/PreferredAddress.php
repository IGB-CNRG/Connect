<?php

namespace App\Entity;

enum PreferredAddress: string
{
    case IGB = 'igb';
    case Home = 'home';
    case Campus = 'campus';
}