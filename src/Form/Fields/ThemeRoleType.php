<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Form\Fields;

use App\Entity\ThemeAffiliation;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeRoleType extends ChoiceType
{
    public const LEADER = 0;
    public const ADMIN = 1;
    public const LAB_MANAGER = 2;

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'required' => false,
            'multiple' => true,
            'expanded' => false,
            'attr' => [
                'class' => 'connect-select2',
            ],
            'choices' => [
                'Theme Leader' => self::LEADER,
                'Theme Admin' => self::ADMIN,
                'Lab Manager' => self::LAB_MANAGER,
            ],
            'getter' => function (ThemeAffiliation $themeAffiliation, FormInterface $form) {
                $return = [];
                if ($themeAffiliation->getIsThemeLeader()) {
                    $return[] = self::LEADER;
                }
                if ($themeAffiliation->getIsThemeAdmin()) {
                    $return[] = self::ADMIN;
                }
                if ($themeAffiliation->getIsLabManager()) {
                    $return[] = self::LAB_MANAGER;
                }
                return $return;
            },
            'setter' => function (ThemeAffiliation &$themeAffiliation, $roles, FormInterface $form) {
                $themeAffiliation->setIsThemeLeader(in_array(self::LEADER, $roles))
                    ->setIsThemeAdmin(in_array(self::ADMIN, $roles))
                    ->setIsLabManager(in_array(self::LAB_MANAGER, $roles))
                ;
            }
        ]);
    }
}