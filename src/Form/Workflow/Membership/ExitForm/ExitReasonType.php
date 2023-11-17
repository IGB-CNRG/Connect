<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\ExitForm;

use App\Entity\ExitReason;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExitReasonType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => ExitReason::class,
            'choice_value' => 'name',
        ]);
    }
}
