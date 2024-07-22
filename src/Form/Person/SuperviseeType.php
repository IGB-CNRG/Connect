<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use App\Repository\PersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                /** @var SupervisorAffiliation $superviseeAffiliation */
                $superviseeAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$superviseeAffiliation || $superviseeAffiliation->getId() === null
                    || $this->security->isGranted('PERSON_EDIT_HISTORY', $superviseeAffiliation->getSupervisor())) {
                    $form->add('supervisee', EntityType::class, [
                        'class' => Person::class,
                        'attr' => [
                            'data-controller' => 'tom-select',
                            'data-tom-select-open-on-focus-value' => 'false',
                        ],
                        'placeholder' => '',
                        'query_builder' => function(PersonRepository $repository){
                            return $repository->createSortedQueryBuilder();
                        },
                    ])->add('startedAt', StartDateType::class, [
                        'required' => (!$superviseeAffiliation || $superviseeAffiliation->getId() === null),
                    ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupervisorAffiliation::class,
        ]);
    }
}
