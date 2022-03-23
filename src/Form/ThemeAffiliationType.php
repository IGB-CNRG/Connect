<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form;

use App\Entity\Person;
use App\Entity\ThemeAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use App\Form\Fields\ThemeRoleType;
use App\Service\HistoricityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeAffiliationType extends AbstractType
{
    public function __construct(public HistoricityManager $historicityManager)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Person $person */
        $person = $options['person'];
        $builder
            ->add('startedAt', StartDateType::class)
            ->add('endedAt', EndDateType::class)
            ->add('theme')
            ->add('memberCategory')
            ->add('title', TextType::class, [
                'required' => false,
                'help' => 'Optional',
            ])
            ->add('endPreviousAffiliations', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'class' => ThemeAffiliation::class,
                'choices' => $this->historicityManager->getCurrentEntities($person->getThemeAffiliations())->toArray(),
            ])
            ->add('specialRole', ThemeRoleType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ThemeAffiliation::class,
        ]);
        $resolver->setRequired([
            'person',
        ]);
    }
}
