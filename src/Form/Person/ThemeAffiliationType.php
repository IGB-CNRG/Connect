<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\ThemeAffiliation;
use App\Entity\ThemeRole;
use App\Form\Fields\EndDateType;
use App\Form\Fields\HistoricalCollectionType;
use App\Form\Fields\MemberCategoryType;
use App\Form\Fields\PositionWhenJoinedType;
use App\Form\Fields\StartDateType;
use App\Form\Fields\ThemeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This is the form only for the Person Edit form
 */
class ThemeAffiliationType extends AbstractType
{
    public function __construct(private readonly Security $security) {}

    public function getBlockPrefix(): string
    {
        return 'ThemeAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var ThemeAffiliation $themeAffiliation */
                $themeAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$themeAffiliation || $themeAffiliation->getId() === null
                    || $this->security->isGranted('PERSON_EDIT_HISTORY', $themeAffiliation->getPerson())) {
                    $form
                        ->add('theme', ThemeType::class)
                        ->add('memberCategory', MemberCategoryType::class)
                        ->add('title', TextType::class, [
                            'required' => false,
                            'help' => 'Optional',
                        ])
                        ->add('roles', EntityType::class, [
                            'class' => ThemeRole::class,
                            'multiple' => true,
                            'required' => false,
                            'attr' => [
                                'data-controller' => 'tom-select',
                            ],
                        ])
                        ->add('sponsorAffiliations', HistoricalCollectionType::class, [
                            'entry_type' => SponsorType::class,
                            'label' => 'Faculty Sponsor(s)'
                        ])
                        ->add('supervisorAffiliations', HistoricalCollectionType::class, [
                            'entry_type' => SupervisorType::class,
                            'label'=> 'Supervisor(s)'
                        ])
                        ->add('startedAt', StartDateType::class);
                }
            });
        if($options['show_position_when_joined']){
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
