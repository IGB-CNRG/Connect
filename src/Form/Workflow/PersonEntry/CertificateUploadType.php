<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\PersonEntry;

use App\Service\CertificateHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CertificateUploadType extends AbstractType
{
    public function __construct(private CertificateHelper $certificateHelper) {}
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
