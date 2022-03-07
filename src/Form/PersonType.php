<?php

namespace App\Form;

use App\Entity\Building;
use App\Entity\Person;
use App\Entity\PreferredAddress;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Form\Type\VichFileType;

class PersonType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
            ])
            ->add('middleInitial', TextType::class, [
                'required' => false,
            ])
            ->add('preferredFirstName', TextType::class, [
                'required' => false,
            ])
            ->add('netid', TextType::class, [
                'required' => false,
                'label' => 'NetID',
            ])
            ->add('username', TextType::class, [
                'required' => false,
            ])
            ->add('uin', TextType::class, [
                'required' => false,
                'label' => 'UIN',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
            ])
            ->add('officeNumber', TextType::class, [
                'required' => false,
                'help' => 'Non-IGB campus address room number'
            ])
            ->add('officePhone', TextType::class, [
                'required' => false,
            ])
            ->add('homeAddress') // todo only display if appropriate
            ->add('isDrsTrainingComplete', CheckboxType::class, [
                'required' => false,
                'label' => 'DRS Training Complete',
            ])
            ->add('isIgbTrainingComplete', CheckboxType::class, [
                'required' => false,
                'label' => 'IGB Training Complete',
            ])
            ->add('offerLetterDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Offer Letter Date',
            ])// todo only display if member is faculty/affiliate?
            ->add('preferredAddress', EnumType::class, [
                'class' => PreferredAddress::class,
            ])
            ->add('officeBuilding', EntityType::class, [
                'required' => false,
                'class' => Building::class,
                'help' => 'Non-IGB campus address building',
            ])
            ->add('imageFile', VichFileType::class, [
                'required' => false,
                'download_uri' => false,
                'allow_delete' => true,
                'label' => 'Portrait',
            ])
        ;
        // todo hide fields based on user roles
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'choices' => Person::USER_ROLES,
                    'multiple' => true,
                    'attr' => [
                        'class' => 'connect-select2'
                    ]
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
