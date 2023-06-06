<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Theme;

use App\Entity\MemberCategory;
use App\Entity\Unit;
use App\Repository\MemberCategoryRepository;
use App\Repository\UnitRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
                'required' => false,
            ])
            ->add('unit', EntityType::class, [
                'class' => Unit::class,
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-placeholder' => 'Filter by unit',
                    'data-action' => 'datatables#columnSearch',
                    'data-column' => 3,
                    'style' => 'width:100%',
                ],
                'choice_value' => fn(Unit $unit) => $unit->__toString(),
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
            // Configure your form options here
        ]);
    }
}
