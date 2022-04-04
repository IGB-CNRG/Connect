<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form;

use App\Entity\Person;
use App\Form\Fields\HistoricalCollectionType;
use App\Form\Person\KeyAffiliationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KeysType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('keyAffiliations', HistoricalCollectionType::class, [
                'entry_type' => KeyAffiliationType::class,
            ])
            ->add('hasGivenKeyDeposit', CheckboxType::class, [
                'required' => false,
                'label' => 'Key Deposit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
