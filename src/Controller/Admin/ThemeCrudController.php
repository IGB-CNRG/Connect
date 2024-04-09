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
            ->setEntityLabelInSingular('Theme')
            ->setEntityLabelInPlural('Themes')
            ->setDefaultSort(['shortName' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('shortName'),
            TextField::new('fullName'),
            BooleanField::new('isNonResearch')->onlyOnForms(),
            BooleanField::new('isOutsideGroup')->onlyOnForms(),
            AssociationField::new('parentTheme')->setFormTypeOptions([
                'query_builder' => function (ThemeRepository $themeRepository) {
                    return $themeRepository->createFormSortedQueryBuilder()->andWhere('t.parentTheme is null');
                },
            ])->setRequired(false),
            DateField::new('startedAt')->hideOnIndex(),
            DateField::new('endedAt'),
        ];
    }
}
