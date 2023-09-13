<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\EntryForm;

use App\Entity\Building;
use App\Entity\Person;
use App\Form\Fields\HistoricalCollectionType;
use App\Form\Fields\UnitType;
use App\Repository\BuildingRepository;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'help' => 'As it appears on your passport, driver\'s license, or I-Card',
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
            ])
            ->add('preferredFirstName', TextType::class, [
                'required' => false,
                'help' => 'Leave blank if same as first name'
            ])
            ->add('netid', TextType::class, [
                'required' => false,
                'label' => 'person.netid',
                'help' => 'If you have not yet been assigned a netid, leave blank',
            ])
            ->add('uin', TextType::class, [
                'required' => !$options['allow_skip_uin'],
                'label' => 'UIN',
                'help' => '9-digit number, found on your I-Card',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'help' => 'Please use Illinois email if you have one'
            ])
            ->add('officeWorkOnly', CheckboxType::class, [
                'required' => false,
                'label' => 'Office work only?',
                'help' => 'Check this box if you will not be working in a lab'
            ])
            ->add('roomAffiliations', CollectionType::class, [
                'entry_type' => RoomAffiliationType::class,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => false,
                'allow_delete' => false,
            ])
            ->add('officePhone', TextType::class, [
                'required' => false,
                'label' => 'entry_form.phone',
            ])
            ->add('unit', UnitType::class, [
                'label' => 'entry_form.unit',
            ])
            ->add('otherUnit', TextType::class, [
                'label' => 'Other department',
                'required' => false,
                'attr' => [
                    'data-other-entry-target' => 'other',
                ],
            ])
            // note we no longer collect dept phone (which should be the same as igb phone anyway)
            //  or cell phones (as we no longer collect home address, etc.)
            ->add('themeAffiliations', HistoricalCollectionType::class, [
                'entry_type' => ThemeAffiliationType::class,
                'entry_options' => [
                    'show_position_when_joined' => $options['show_position_when_joined'],
                ],
                'required' => true,
                'allow_delete' => false,
            ])
            ->add('officeNumber', TextType::class, [
                'required' => false,
                'help' => 'Non-IGB campus address room number',
            ])
            ->add('officeBuilding', EntityType::class, [
                'required' => false,
                'label' => 'Building',
                'class' => Building::class,
                'help' => 'Non-IGB campus address building',
                'placeholder' => 'Other (please specify)',
                'attr' => [
                    'data-controller' => 'tom-select',
                    'data-other-entry-target' => 'select',
                    'data-action' => 'change->other-entry#toggle',
                ],
                'query_builder' => fn(BuildingRepository $repository) => $repository->createQueryBuilderForDropdown(),
            ])
            ->add('otherAddress', TextareaType::class, [
                'label' => 'Other address',
                'required' => false,
                'attr' => [
                    'data-other-entry-target' => 'other',
                ],
            ])
        ;
        if ($options['use_captcha']) {
            // only show captcha when we're not logged in
            $builder->add('captcha', CaptchaType::class);
        }
        if ($options['allow_silent']) {
//            $builder->add('isSilent', CheckboxType::class, [
//                'mapped' => false,
//                'required' => false,
//                'label' => 'Create member silently',
//                'help' => 'Check this box to bypass the rest of the new member workflow without sending any further notifications',
//            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ])
            ->setRequired([
                'allow_silent',
                'show_position_when_joined',
                'allow_skip_uin',
                'use_captcha',
            ]);
    }
}
