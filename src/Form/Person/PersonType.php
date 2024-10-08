<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\Building;
use App\Entity\Person;
use App\Form\Fields\HistoricalCollectionType;
use App\Form\Fields\UnitType;
use App\Repository\BuildingRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class PersonType extends AbstractType
{
    public function __construct(private Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
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
                'help' => 'Non-IGB campus address room number',
            ])
            ->add('officePhone', TextType::class, [
                'required' => false,
            ])
            ->add('officeBuilding', EntityType::class, [
                'required' => false,
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
            ->add('imageFile', VichFileType::class, [
                'required' => false,
                'download_uri' => false,
                'allow_delete' => true,
                'delete_label' => 'Remove current portrait',
                'label' => 'Portrait',
            ])
            ->add('themeAffiliations', HistoricalCollectionType::class, [
                // todo check out delete_empty
                'entry_type' => ThemeAffiliationType::class,
                'entry_options' => [
                    'show_position_when_joined' => $options['show_position_when_joined'],
                ],
                'required' => true,
            ])
            ->add('roomAffiliations', HistoricalCollectionType::class, [
                'entry_type' => RoomAffiliationType::class,
                'required' => true,
            ])
            ->add('unit', UnitType::class)
            ->add('otherUnit', TextType::class, [
                'label' => 'Other department',
                'required' => false,
                'attr' => [
                    'data-other-entry-target' => 'other',
                ],
            ])
            ->add('officeWorkOnly', CheckboxType::class, [
                'required' => false,
                'label' => 'Office work only?',
                'help' => 'Check this box if this person will not be working in a lab'
            ]);
        // show/hide fields based on user roles
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'choices' => Person::USER_ROLES,
                    'multiple' => true,
                    'attr' => [
                        'data-controller' => 'tom-select',
                    ],
                    'required' => false,
                ])
                ->add('hideFromDirectory', CheckboxType::class, [
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
        ])->setRequired([
            'show_position_when_joined',
        ]);
    }
}
