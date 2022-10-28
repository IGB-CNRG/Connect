<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\PersonEntry;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CertificateUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('drs', CertificateType::class, [
                'label' => 'DRS Training Certificate',
            ])
            ->add('igb', CertificateType::class, [
                'label' => 'IGB Training Certificate',
            ])
        ;
    }
}
