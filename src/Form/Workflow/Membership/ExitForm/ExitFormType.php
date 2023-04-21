<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Workflow\Membership\ExitForm;

use App\Entity\ExitForm;
use App\Form\Fields\EndDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endedAt', EndDateType::class, [
                'data' => new \DateTimeImmutable(),
                'required' => true,
                'help' => null,
                'input'=>'datetime_immutable',
            ])
            ->add('forwardingEmail', EmailType::class, [
                'required' => !$options['force'],
            ])
        ;
        if($options['force']){
            $builder->add('exitReason', ExitReasonType::class);
        } else {
            $builder->add('exitReason');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExitForm::class,
        ]);
        $resolver->setRequired([
            'force'
        ]);
    }
}
