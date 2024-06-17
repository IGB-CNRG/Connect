<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\EntryForm;

use App\Entity\Theme;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeAffiliationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Only populate the form for new entries or current entries. We don't want to expose past
        //  affiliations on the entry form.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var ThemeAffiliation $affiliation */
            $affiliation = $event->getData();
            $form = $event->getForm();

            if(!$affiliation || !$affiliation->isPast()){
                $form
                    ->add('theme', ThemeType::class, [
                        'query_builder' => function (ThemeRepository $themeRepository) {
                            return $themeRepository->createCurrentFormSortedQueryBuilder();
                        },
                        'choice_label' => function (Theme $theme) {
                            $label = $theme->getFullName();
                            if($theme->getShortName() !== $theme->getFullName()){ // todo can we make short name optional?
                                $label = $theme->getShortName().' - '.$label;
                            }
                            if($theme->getParentTheme()){
                                $label = $label . " ({$theme->getParentTheme()->getShortName()})";
                            }
                            return $label;
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
                    $form->add('positionWhenJoined', PositionWhenJoinedType::class);
                }
            }
        });
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