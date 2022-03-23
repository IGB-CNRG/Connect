<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SuperviseeType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function getBlockPrefix(): string
    {
        return 'SuperviseeAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $superviseeAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$superviseeAffiliation || $superviseeAffiliation->getId() === null) {
                    $form->add('supervisee', EntityType::class, [
                        'class' => Person::class,
                        'attr' => [
                            'class' => 'connect-select2',
                        ],
                    ]);
                }
                if ($this->security->isGranted('PERSON_EDIT_HISTORY')
                    || !$superviseeAffiliation
                    || $superviseeAffiliation->getId() === null) {
                    $form->add('startedAt', StartDateType::class);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupervisorAffiliation::class,
        ]);
    }
}
