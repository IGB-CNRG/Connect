<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\EntryForm;

use App\Entity\Building;
use App\Entity\Person;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryFormType extends AbstractType
{
    public function __construct(private readonly Security $security) {}
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
            ->add('netid', TextType::class, [
                'required' => false,
                'label' => 'person.netid',
            ])
            ->add('uin', TextType::class, [
                'required' => false,
                'label' => 'UIN',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('officeWorkOnly', CheckboxType::class, [
                'required' => false,
                'label' => 'Office work only?',
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
            ->add('unitAffiliations', CollectionType::class, [
                'entry_type' => UnitAffiliationType::class,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => false,
                'allow_delete' => false,
            ])
            ->add('supervisorAffiliations', CollectionType::class, [
                'entry_type' => SupervisorAffiliationType::class,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => false,
                'allow_delete' => false,
            ])
            // note we no longer collect dept phone (which should be the same as igb phone anyway)
            //  or cell phones (as we no longer collect home address, etc.)
            ->add('themeAffiliations', CollectionType::class, [
                'entry_type' => ThemeAffiliationType::class,
                'label'=> false,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => false,
                'allow_delete' => false,
            ])

            ->add('officeNumber', TextType::class, [
                'required' => false,
                'help' => 'Non-IGB campus address room number'
            ])
            ->add('officeBuilding', EntityType::class, [
                'required' => false,
                'class' => Building::class,
                'help' => 'Non-IGB campus address building',
            ])
            ;
        if(!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            // only show captcha when we're not logged in
            $builder->add('captcha', CaptchaType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
