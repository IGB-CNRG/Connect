<?php

namespace App\Form\Fields;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StartDateType extends DateType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'label' => 'Start date',
            'widget' => 'single_text',
        ]);
    }
}