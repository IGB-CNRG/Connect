<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form;

use App\Entity\MemberCategory;
use App\Form\Fields\ThemeType;
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
                    'data-controller' => 'select2',
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
                    'data-controller' => 'select2',
                    'data-placeholder' => 'Filter by employee type',
                    'data-action' => 'datatables#columnSearch',
                    'style' => 'width:100%',
                ],
                'choice_value' => function(MemberCategory $category){
                    return $category->getShortName()??$category->getName();
                },
                'query_builder' => function(MemberCategoryRepository $repository){
                    return $repository->createFormSortedQueryBuilder();
                },
            ])
            ->add('role', ChoiceType::class, [
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'select2',
                    'data-placeholder' => 'Filter by employee type',
                    'data-action' => 'datatables#columnSearch',
                    'style' => 'width:100%',
                ],
                'choices' => ['Theme Leader'=>'Theme Leader', 'Theme Admin'=>'Theme Admin', 'Lab Manager'=>'Lab Manager'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
