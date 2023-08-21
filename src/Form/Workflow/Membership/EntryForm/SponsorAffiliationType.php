<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\EntryForm;

use App\Entity\Person;
use App\Entity\SponsorAffiliation;
use App\Repository\PersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SponsorAffiliationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sponsor', EntityType::class, [
                'class' => Person::class,
                'attr' => [
                    'autocomplete' => false,
                    'data-controller' => 'tom-select',
                ],
                'required' => false,
                'label' => 'entry_form.sponsor',
                'query_builder' => function(PersonRepository $repository){
                    return $repository->createSupervisorDropdownQueryBuilder();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SponsorAffiliation::class,
        ]);
    }
}