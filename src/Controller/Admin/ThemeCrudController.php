<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Theme;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ThemeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Theme::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewConnect = Action::new('viewConnect', 'View in CONNECT', 'fa fa-eye')
            ->linkToRoute('theme_view', function(Theme $theme){
                return ['shortName'=>$theme->getShortName()];
            });
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $viewConnect);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Theme')
            ->setEntityLabelInPlural('Themes')//            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('shortName'),
            TextField::new('fullName'),
            BooleanField::new('isNonResearch')->renderAsSwitch(false),
            BooleanField::new('isOutsideGroup')->renderAsSwitch(false),
            DateField::new('startedAt'),
            DateField::new('endedAt'),
        ];
    }
}
