<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\ExitForm;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExitReasonType extends ChoiceType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'choices' => [
                'Resigned' => 'Resigned',
                'Graduated' => 'Graduated',
                //                    'Terminated' => 'Terminated',
                //                    'Let go' => 'Let go',
                'Retired' => 'Retired',
                'Deceased' => 'Deceased',
            ],
        ]);
    }
}
