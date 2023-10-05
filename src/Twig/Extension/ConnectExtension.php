<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig\Extension;

use App\Twig\Runtime\ConnectRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ConnectExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('current', [ConnectRuntime::class, 'getCurrent']),
            new TwigFilter('currentAndFuture', [ConnectRuntime::class, 'getCurrentAndFuture']),
            new TwigFilter('member', [ConnectRuntime::class, 'getMember']),
            new TwigFilter('role', [ConnectRuntime::class, 'getRoleName']),
            new TwigFilter('theme', [ConnectRuntime::class, 'filterByTheme']),
            new TwigFilter('earliest', [ConnectRuntime::class, 'earliest']),
            new TwigFilter('latest', [ConnectRuntime::class, 'latest']),

        ];
    }

    public function getFunctions(): array
    {
        return [

        ];
    }
}
