<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\MemberCategory;
use App\Entity\ThemeRole;
use App\Entity\Unit;
use App\Form\Fields\ThemeType;
use App\Repository\MemberCategoryRepository;
use App\Repository\UnitRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', SearchType::class, [
                'attr' => [
                    'data-action' => 'keydown.enter->autosubmit#submit', // todo when we add turbo, we can add autosubmit#debouncedSubmit
                    'placeholder' => 'Search',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('pageSize', ChoiceType::class, [
                'choices' => [
                    '10 per page'=>10,
                    '25 per page'=>25,
                    '50 per page'=>50,
                    '100 per page'=>100
                ],
                'attr' => [
                    'data-action' => 'autosubmit#submit',
                ]
            ])
            ->add('theme', ThemeType::class, [
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-placeholder' => 'Filter by theme',
                    'data-action' => 'autosubmit#submit',
                    'style' => 'width:100%',
                ],
                'required' => false,
            ])
            ->add('employeeType', EntityType::class, [
                'class' => MemberCategory::class,
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-placeholder' => 'Filter by employee type',
                    'data-action' => 'autosubmit#submit',
                    'style' => 'width:100%',
                ],
                'query_builder' => function (MemberCategoryRepository $repository) {
                    return $repository->createFormSortedQueryBuilder();
                },
                'required' => false,
            ])
            ->add('role', EntityType::class, [
                'class' => ThemeRole::class,
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-placeholder' => 'Filter by employee type',
                    'data-action' => 'autosubmit#submit',
                    'style' => 'width:100%',
                ],
                'required' => false,
            ])
            ->add('unit', EntityType::class, [
                'class' => Unit::class,
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-placeholder' => 'Filter by unit',
                    'data-action' => 'autosubmit#submit',
                    'style' => 'width:100%',
                ],
                'query_builder' => fn(UnitRepository $unitRepository) => $unitRepository->createFormSortedQueryBuilder(),
                'choice_filter' => fn(Unit $unit) => $unit->getChildUnits()->count()===0,
                'group_by' => function (Unit $choice, $key, $value) {
                    if ($choice->getParentUnit()) {
                        return $choice->getParentUnit();
                    } else {
                        return null;
                    }
                },
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
