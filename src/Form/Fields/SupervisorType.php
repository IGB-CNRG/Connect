<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Fields;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupervisorType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => Person::class,
            'attr' => [
                'autocomplete' => false,
                'data-controller' => 'tom-select',
            ],
            'required' => false,
            'multiple' => true,
            'query_builder' => function (PersonRepository $repository) {
                return $repository->createSupervisorDropdownQueryBuilder();
            },
        ]);
    }
}