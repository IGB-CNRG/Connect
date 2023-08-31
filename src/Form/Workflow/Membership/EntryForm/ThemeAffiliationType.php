<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\EntryForm;

use App\Entity\ThemeAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\HistoricalCollectionType;
use App\Form\Fields\MemberCategoryType;
use App\Form\Fields\PositionWhenJoinedType;
use App\Form\Fields\StartDateType;
use App\Form\Fields\ThemeType;
use App\Repository\ThemeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeAffiliationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('theme', ThemeType::class, [
                'query_builder' => function (ThemeRepository $themeRepository) {
                    return $themeRepository->createCurrentFormSortedQueryBuilder();
                },
            ])
            ->add('memberCategory', MemberCategoryType::class, [
                'label' => 'entry_form.member_category',
            ])
            ->add('sponsorAffiliations', HistoricalCollectionType::class, [
                'entry_type' => SponsorAffiliationType::class,
            ])
            ->add('supervisorAffiliations', HistoricalCollectionType::class, [
                'entry_type' => SupervisorAffiliationType::class,
            ])
            ->add('startedAt', StartDateType::class)
            ->add('endedAt', EndDateType::class);
        if ($options['show_position_when_joined']) {
            $builder->add('positionWhenJoined', PositionWhenJoinedType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ThemeAffiliation::class,
        ])->setRequired([
            'show_position_when_joined',
        ]);
    }
}