<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Fields;

use App\Entity\Department;
use App\Repository\DepartmentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => Department::class,
            'attr' => [
                'data-controller' => 'tom-select',
                'data-other-entry-target' => 'select',
                'data-action' => 'change->other-entry#toggle',
            ],
            'required' => false,
            'placeholder' => 'Other (please specify)',
            'group_by' => function (Department $choice, $key, $value) {
                if ($choice->getCollege()) {
                    return $choice->getCollege();
                } else {
                    return null;
                }
            },
            'query_builder' => function (DepartmentRepository $departmentRepository) {
                return $departmentRepository->createFormSortedQueryBuilder();
            }
        ]);
    }
}