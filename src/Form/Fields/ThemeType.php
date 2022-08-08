<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Fields;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => Theme::class,
            'attr' => [
                'data-controller' => 'select2',
            ],
            'query_builder' => function (ThemeRepository $themeRepository) {
                return $themeRepository->createFormSortedQueryBuilder();
            },
            'group_by' => function (Theme $choice, $key, $value){
                if($choice->getIsOutsideGroup()){
                    return 'Outside Groups';
                }
                if($choice->getIsNonResearch()){
                    return 'Non-research';
                }
                return 'Research Themes';
            }
        ]);
    }
}