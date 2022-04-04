<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\Department;
use App\Entity\DepartmentAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentAffiliationType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'DepartmentAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startedAt', StartDateType::class)
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $departmentAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$departmentAffiliation || $departmentAffiliation->getId() === null) {
                    $form->add('department', EntityType::class, [
                        'class' => Department::class,
                        'attr' => [
                            'class' => 'connect-select2',
                        ],
                    ]);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DepartmentAffiliation::class,
        ]);
    }
}
