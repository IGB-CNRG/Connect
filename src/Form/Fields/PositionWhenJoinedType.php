<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Fields;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionWhenJoinedType extends ChoiceType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'label' => 'person.positionWhenJoined',
            'placeholder' => '',
            'choices' => [
                'Assistant Professor' => 'Assistant Professor',
                'Associate Professor' => 'Associate Professor',
                'Professor' => 'Professor',
            ],
            'required' => false,
        ]);
    }
}