<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\EntryForm;

use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Repository\PersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupervisorAffiliationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // todo update this to work like the other supervisor dropdown after merge
            ->add('supervisor', EntityType::class, [
                'class' => Person::class,
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-tom-select-open-on-focus-value' => 'false',
                ],
                'required' => false,
                'label' => 'entry_form.supervisor',
                'query_builder' => function(PersonRepository $repository){
                    return $repository->createSupervisorDropdownQueryBuilder();
                },
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupervisorAffiliation::class,
        ]);
    }
}