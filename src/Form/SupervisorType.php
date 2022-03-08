<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use App\Service\HistoricityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupervisorType extends AbstractType
{
    public function __construct(public HistoricityManager $historicityManager){}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Person $person */
        $person = $options['person'];
        $builder
            ->add('startedAt', StartDateType::class)
            ->add('endedAt', EndDateType::class)
            ->add('supervisor')// todo who are allowed to be supervisors? everyone?
            ->add('endPreviousAffiliations', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'class' => SupervisorAffiliation::class,
                'choices' =>$this->historicityManager->getCurrentEntities($person->getSupervisorAffiliations())->toArray(),
                'choice_label' => 'supervisor',
            ])
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
