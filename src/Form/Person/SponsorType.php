<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Person;

use App\Entity\Person;
use App\Entity\SponsorAffiliation;
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

class SponsorType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function getBlockPrefix(): string
    {
        return 'SponsorAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var SponsorAffiliation $sponsorAffiliation */
                $sponsorAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$sponsorAffiliation || $sponsorAffiliation->getId() === null
                    || $this->security->isGranted('PERSON_EDIT_HISTORY', $sponsorAffiliation->getSponsee())) {
                    $form->add('sponsor', EntityType::class, [
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
                        'required' => (!$sponsorAffiliation || $sponsorAffiliation->getId() === null),
                    ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SponsorAffiliation::class,
        ]);
    }
}
