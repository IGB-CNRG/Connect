<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\Theme;
use App\Entity\ThemeAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use App\Form\Fields\ThemeRoleType;
use App\Repository\ThemeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ThemeAffiliationType extends AbstractType
{
    public function __construct(private Security $security) { }

    public function getBlockPrefix(): string
    {
        return 'ThemeAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $themeAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$themeAffiliation || $themeAffiliation->getId() === null) {
                    $form
                        ->add('theme', EntityType::class, [
                            'class' => Theme::class,
                            'attr' => [
                                'data-controller' => 'select2',
                            ],
                            'query_builder' => function (ThemeRepository $themeRepository) {
                                return $themeRepository->createFormSortedQueryBuilder();
                            }

                        ])
                        ->add('memberCategory')
                        ->add('title', TextType::class, [
                            'required' => false,
                            'help' => 'Optional',
                        ])
                        ->add('specialRole', ThemeRoleType::class)
                    ;
                }
                if ($this->security->isGranted('PERSON_EDIT_HISTORY')
                    || !$themeAffiliation
                    || $themeAffiliation->getId() === null) {
                    $form->add('startedAt', StartDateType::class);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ThemeAffiliation::class,
        ]);
    }
}
