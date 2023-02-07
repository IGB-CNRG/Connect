<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form;

use App\Entity\Department;
use App\Entity\MemberCategory;
use App\Form\Fields\ThemeType;
use App\Repository\DepartmentRepository;
use App\Repository\MemberCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvancedSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('theme', ThemeType::class, [
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-placeholder' => 'Filter by theme',
                    'data-action' => 'datatables#columnSearch',
                    'style' => 'width:100%',
                ],
                'choice_value' => 'shortName',
            ])
            ->add('employeeType', EntityType::class, [
                'class' => MemberCategory::class,
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-placeholder' => 'Filter by employee type',
                    'data-action' => 'datatables#columnSearch',
                    'style' => 'width:100%',
                ],
                'choice_value' => function (MemberCategory $category) {
                    return $category->getShortName() ?? $category->getName();
                },
                'query_builder' => function (MemberCategoryRepository $repository) {
                    return $repository->createFormSortedQueryBuilder();
                },
            ])
            ->add('role', ChoiceType::class, [
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-placeholder' => 'Filter by employee type',
                    'data-action' => 'datatables#columnSearch',
                    'style' => 'width:100%',
                ],
                'choices' => [
                    'Theme Leader' => 'Theme Leader',
                    'Theme Admin' => 'Theme Admin',
                    'Lab Manager' => 'Lab Manager'
                ],
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-placeholder' => 'Filter by department',
                    'data-action' => 'datatables#columnSearch',
                    'data-column' => 3,
                    'style' => 'width:100%',
                ],
                'choice_value' => fn(Department $department) => $department->__toString(),
                'query_builder' => fn(DepartmentRepository $departmentRepository) => $departmentRepository->createFormSortedQueryBuilder(),
                'group_by' => function (Department $choice, $key, $value) {
                    if ($choice->getCollege()) {
                        return $choice->getCollege();
                    } else {
                        return null;
                    }
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
