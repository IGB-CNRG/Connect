<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form;

use App\Entity\DepartmentAffiliation;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EndDepartmentAffiliationType extends EndAffiliationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DepartmentAffiliation::class,
        ]);
    }
}
