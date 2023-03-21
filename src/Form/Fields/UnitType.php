<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Fields;

use App\Entity\Unit;
use App\Repository\UnitRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => Unit::class,
            'attr' => [
                'data-controller' => 'tom-select',
                'data-other-entry-target' => 'select',
                'data-action' => 'change->other-entry#toggle',
            ],
            'required' => false,
            'placeholder' => 'Other (please specify)',
            'group_by' => function (Unit $choice, $key, $value) {
                if ($choice->getParentUnit()) {
                    return $choice->getParentUnit();
                } else {
                    return null;
                }
            },
            'choice_filter' => fn(?Unit $unit) => $unit!==null && $unit->getChildUnits()->count()===0,
            'query_builder' => function (UnitRepository $unitRepository) {
                return $unitRepository->createFormSortedQueryBuilder();
            }
        ]);
    }
}