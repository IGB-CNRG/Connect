<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use App\Service\HistoricityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuperviseeType extends AbstractType
{
    public function __construct(public HistoricityManager $historicityManager){}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Person $person */
        $person = $options['person'];
        $builder
            ->add('startedAt', StartDateType::class)
            ->add('endedAt', EndDateType::class)
            ->add('supervisee')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupervisorAffiliation::class,
        ]);
        $resolver->setRequired([
            'person',
        ]);
    }
}
