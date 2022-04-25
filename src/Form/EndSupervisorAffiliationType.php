<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form;

use App\Entity\SupervisorAffiliation;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EndSupervisorAffiliationType extends EndAffiliationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupervisorAffiliation::class,
        ]);
    }
}
