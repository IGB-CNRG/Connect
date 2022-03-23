<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Fields;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoricalCollectionType extends CollectionType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'entry_options' => [
                'label' => false,
            ],
            'label'=>false,
            'allow_add' => true,
            'allow_delete' => false,
            'by_reference' => false,
            'prototype' => true,
            'required' => false,
        ]);
    }
}