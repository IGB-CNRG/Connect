<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Twig\Extension;

use App\Entity\Person;
use App\Workflow\Twig\Runtime\WorkflowRuntime;
use Symfony\Component\Workflow\WorkflowInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class WorkflowExtension extends AbstractExtension
{
    public function __construct(private readonly WorkflowInterface $membershipStateMachine) {}

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('workflow_place_label', [$this, 'label']),
            new TwigFilter('workflow_place_completion_message', [$this, 'message']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('workflow_place_label', [$this, 'label']),
            new TwigFunction('workflow_place_completion_message', [$this, 'message']),
            new TwigFunction('workflow_approvers', [WorkflowRuntime::class, 'getMembershipApprovers']),
        ];
    }

    public function label(Person $value): string
    {
        return 'membership.' . array_key_first($this->membershipStateMachine->getMarking($value)->getPlaces())
               . '.label';
    }

    public function message(Person $value): string
    {
        return 'membership.' . array_key_first($this->membershipStateMachine->getMarking($value)->getPlaces())
               . '.completion_message';
    }
}
