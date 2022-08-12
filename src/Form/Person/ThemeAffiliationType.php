<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\MemberCategory;
use App\Entity\ThemeAffiliation;
use App\Enum\ThemeRole;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use App\Form\Fields\ThemeType;
use App\Repository\MemberCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ThemeAffiliationType extends AbstractType
{
    public function __construct(private Security $security) {}

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
                        ->add('theme', ThemeType::class)
                        ->add('memberCategory', EntityType::class, [
                            'class' => MemberCategory::class,
                            'query_builder' => function(MemberCategoryRepository $repository){
                                return $repository->createFormSortedQueryBuilder();
                            },
                        ])
                        ->add('title', TextType::class, [
                            'required' => false,
                            'help' => 'Optional',
                        ])
                        ->add('themeRoles', EnumType::class, [
                            'class' => ThemeRole::class,
                            'multiple' => true,
                            'required' => false,
                            'attr' => [
                                'data-controller' => 'select2',
                            ],
                        ]);
                }
                if ($this->security->isGranted('PERSON_EDIT_HISTORY')
                    || !$themeAffiliation
                    || $themeAffiliation->getId() === null) {
                    $form->add('startedAt', StartDateType::class);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ThemeAffiliation::class,
        ]);
    }
}
