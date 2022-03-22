<?php

namespace App\Form\Person;

use App\Entity\Room;
use App\Entity\RoomAffiliation;
use App\Form\Fields\EndDateType;
use App\Form\Fields\StartDateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class RoomAffiliationType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function getBlockPrefix(): string
    {
        return 'RoomAffiliation';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endedAt', EndDateType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $roomAffiliation = $event->getData();
                $form = $event->getForm();

                if (!$roomAffiliation || $roomAffiliation->getId() === null) {
                    $form->add('room', EntityType::class, [
                        'class' => Room::class,
                        'attr' => [
                            'class' => 'connect-select2',
                        ],
                    ]);
                }
                if ($this->security->isGranted('PERSON_EDIT_HISTORY')
                    || !$roomAffiliation
                    || $roomAffiliation->getId() === null) {
                    $form->add('startedAt', StartDateType::class);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RoomAffiliation::class,
        ]);
    }
}
