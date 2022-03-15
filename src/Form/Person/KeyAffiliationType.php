<?php

namespace App\Form\Person;

use App\Entity\KeyAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KeyAffiliationType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'KeyAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startedAt', StartDateType::class)
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $keyAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$keyAffiliation || $keyAffiliation->getId() === null) {
                    $form->add('cylinderKey');
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => KeyAffiliation::class,
        ]);
    }
}
