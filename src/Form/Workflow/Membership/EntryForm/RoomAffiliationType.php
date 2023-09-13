<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\EntryForm;

use App\Entity\Room;
use App\Entity\RoomAffiliation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomAffiliationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Only populate the form for new entries or current entries. We don't want to expose past
        //  affiliations on the entry form.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            /** @var RoomAffiliation $affiliation */
            $affiliation = $event->getData();
            $form = $event->getForm();

            if(!$affiliation || $affiliation->isCurrent()){
                $form->add('room', EntityType::class, [
                    'class' => Room::class,
                    'attr' => [
                        'data-controller' => 'tom-select',
                    ],
                    'required' => false,
                    'label' => 'entry_form.room',
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RoomAffiliation::class,
        ]);
    }
}