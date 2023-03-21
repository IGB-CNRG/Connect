<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form;

use App\Entity\UnitAffiliation;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EndUnitAffiliationType extends EndAffiliationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UnitAffiliation::class,
        ]);
    }
}
