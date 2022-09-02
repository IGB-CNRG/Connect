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
use App\Repository\PersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SupervisorType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function getBlockPrefix(): string
    {
        return 'SupervisorAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var SupervisorAffiliation $supervisorAffiliation */
                $supervisorAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$supervisorAffiliation || $supervisorAffiliation->getId() === null
                    || $this->security->isGranted('PERSON_EDIT_HISTORY', $supervisorAffiliation->getSupervisee())) {
                    $form->add('supervisor', EntityType::class, [
                        'class' => Person::class,
                        'attr' => [
                            'data-controller' => 'select2',
                        ],
                        'query_builder' => function(PersonRepository $repository){
                            return $repository->createDropdownQueryBuilder();
                        },
                    ])->add('startedAt', StartDateType::class);
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
