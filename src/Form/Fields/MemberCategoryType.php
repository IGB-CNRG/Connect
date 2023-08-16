<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Fields;

use App\Entity\MemberCategory;
use App\Repository\MemberCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberCategoryType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => MemberCategory::class,
            'placeholder' => '',
            'query_builder' => function (MemberCategoryRepository $repository) {
                return $repository->createFormSortedQueryBuilder();
            },
            'required' => true,
        ]);
    }
}