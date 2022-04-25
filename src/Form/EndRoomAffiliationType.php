<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form;

use App\Entity\RoomAffiliation;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EndRoomAffiliationType extends EndAffiliationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RoomAffiliation::class,
        ]);
    }
}
