<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\ExitForm;

use App\Form\Fields\EndDateType;
use App\Form\Workflow\ApproveType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ExitFormApprovalType extends ApproveType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('exitReason', ChoiceType::class, [
                'choices' => [
                    'Resigned' => 'Resigned',
                    'Graduated' => 'Graduated',
//                    'Terminated' => 'Terminated',
//                    'Let go' => 'Let go',
                    'Retired' => 'Retired',
                    'Deceased' => 'Deceased',
                ],
            ])
            ->add('endedAt', EndDateType::class, [
                'required' => true,
                'help' => null,
            ]);
        parent::buildForm($builder, $options);
    }

}
