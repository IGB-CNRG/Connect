<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Theme;

use Symfony\Component\Form\FormBuilderInterface;

class FilterType extends \App\Form\Person\FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->remove('theme')
            ->remove('role');
    }
}
