<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\UnitAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use App\Form\Fields\UnitType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitAffiliationType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function getBlockPrefix(): string
    {
        return 'UnitAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startedAt', StartDateType::class)
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var UnitAffiliation $unitAffiliation */
                $unitAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$unitAffiliation || $unitAffiliation->getId() === null
                    || $this->security->isGranted('PERSON_EDIT_HISTORY', $unitAffiliation->getPerson())) {
                    $form->add('unit', UnitType::class)
                        ->add('otherUnit', TextType::class, [
                            'label' => 'Other department',
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
            'data_class' => UnitAffiliation::class,
        ]);
    }
}
