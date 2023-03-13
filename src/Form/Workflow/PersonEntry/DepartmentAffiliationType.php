<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\PersonEntry;

use App\Entity\DepartmentAffiliation;
use App\Form\Fields\DepartmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentAffiliationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('department', DepartmentType::class, [
                'label' => 'entry_form.department',
            ])
            ->add('otherDepartment', TextType::class, [
                'required' => false,
                'attr' => [
                    'data-other-entry-target' => 'other',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DepartmentAffiliation::class,
        ]);
    }
}