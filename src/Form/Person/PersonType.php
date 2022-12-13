<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\Building;
use App\Entity\Person;
use App\Enum\PreferredAddress;
use App\Form\Fields\HistoricalCollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Form\Type\VichFileType;

class PersonType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
            ])
            ->add('middleInitial', TextType::class, [
                'required' => false,
            ])
            ->add('preferredFirstName', TextType::class, [
                'required' => false,
            ])
            ->add('netid', TextType::class, [
                'required' => false,
                'label' => 'person.netid',
            ])
            ->add('username', TextType::class, [
                'required' => false,
                'label' => 'IGB Username',
            ])
            ->add('uin', TextType::class, [
                'required' => false,
                'label' => 'UIN',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
            ])
            ->add('officeNumber', TextType::class, [
                'required' => false,
                'help' => 'Non-IGB campus address room number'
            ])
            ->add('officePhone', TextType::class, [
                'required' => false,
            ])
            ->add('isDrsTrainingComplete', CheckboxType::class, [
                'required' => false,
                'label' => 'DRS Training Complete',
            ])
            ->add('isIgbTrainingComplete', CheckboxType::class, [
                'required' => false,
                'label' => 'IGB Training Complete',
            ])
            ->add('offerLetterDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Offer Letter Date',
            ])// todo only display if member is faculty/affiliate?
            ->add('preferredAddress', EnumType::class, [
                'class' => PreferredAddress::class,
            ])
            ->add('officeBuilding', EntityType::class, [
                'required' => false,
                'class' => Building::class,
                'help' => 'Non-IGB campus address building',
            ])
            ->add('imageFile', VichFileType::class, [
                'required' => false,
                'download_uri' => false,
                'allow_delete' => true,
                'delete_label' => 'Remove current portrait',
                'label' => 'Portrait',
            ])
            ->add('themeAffiliations', HistoricalCollectionType::class, [
                'entry_type' => ThemeAffiliationType::class,
            ])
            ->add('supervisorAffiliations', HistoricalCollectionType::class, [
                'entry_type' => SupervisorType::class,
            ])
            ->add('superviseeAffiliations', HistoricalCollectionType::class, [
                'entry_type' => SuperviseeType::class,
            ])
            ->add('roomAffiliations', HistoricalCollectionType::class, [
                'entry_type' => RoomAffiliationType::class,
            ])
            ->add('departmentAffiliations', HistoricalCollectionType::class, [
                'entry_type' => DepartmentAffiliationType::class,
            ]);
        // todo hide fields based on user roles
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'choices' => Person::USER_ROLES,
                    'multiple' => true,
                    'attr' => [
                        'data-controller' => 'select2',
                    ],
                    'required' => false,
                ]);
        }
        if ($this->security->isGranted('ROLE_KEY_MANAGER')) {
            $builder
                ->add('keyAffiliations', HistoricalCollectionType::class, [
                    'entry_type' => KeyAffiliationType::class,
                ])
                ->add('hasGivenKeyDeposit', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Key Deposit',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
