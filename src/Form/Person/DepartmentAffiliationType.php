<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\DepartmentAffiliation;
use App\Form\Fields\DepartmentType;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class DepartmentAffiliationType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function getBlockPrefix(): string
    {
        return 'DepartmentAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startedAt', StartDateType::class)
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var DepartmentAffiliation $departmentAffiliation */
                $departmentAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$departmentAffiliation || $departmentAffiliation->getId() === null
                    || $this->security->isGranted('PERSON_EDIT_HISTORY', $departmentAffiliation->getPerson())) {
                    $form->add('department', DepartmentType::class)
                        ->add('otherDepartment', TextType::class, [
                            'required' => false,
                            'attr' => [
                                'data-other-entry-target' => 'other',
                            ],
                        ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DepartmentAffiliation::class,
        ]);
    }
}
