<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
        $viewConnect = Action::new('viewConnect', 'View in Connect', 'fa fa-eye')
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
            ->setEntityLabelInSingular('Theme/Group')
            ->setEntityLabelInPlural('Themes/Groups')
            ->setDefaultSort(['shortName' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('shortName'),
            TextField::new('fullName'),
            AssociationField::new('themeType'),
            AssociationField::new('parentTheme')->setFormTypeOptions([
                'query_builder' => function (ThemeRepository $themeRepository) {
                    return $themeRepository->createFormSortedQueryBuilder()->andWhere('t.parentTheme is null');
                },
            ])->setRequired(false),
            DateField::new('startedAt')->hideOnIndex(),
            DateField::new('endedAt'),
            AssociationField::new('approvers')->setHelp('Select any <i>additional</i> approvers necessary for this group. Any theme admins, lab managers, etc. should be designated in their respective Connect records.'),
        ];
    }
}
