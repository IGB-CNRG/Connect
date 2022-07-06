<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Person;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'All People')
            ->setEntityLabelInSingular('Person')
            ->setEntityLabelInPlural('People')//            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::EDIT, Action::NEW, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('preferredFirstName');
        yield TextField::new('firstName');
        yield TextField::new('lastName');
        yield TextField::new('netid');
        yield TextField::new('username');
        yield IntegerField::new('uin');
        yield EmailField::new('email');
        yield TelephoneField::new('officePhone');
        yield BooleanField::new('isCurrent')->renderAsSwitch(false);
        yield AssociationField::new('themeAffiliations')->onlyOnDetail()->setTemplatePath(
            'admin/historical_association_comma_separated.html.twig'
        )->setCustomOptions([
            'linkField' => 'theme',
            'linkCrudFqcn' => ThemeCrudController::class,
        ]);
        if($this->isGranted('ROLE_KEY_MANAGER')) {
            yield AssociationField::new('keyAffiliations')
                ->onlyOnDetail()
                ->setLabel('Key Assignments')
                ->setTemplatePath(
                    'admin/historical_association_comma_separated.html.twig'
                )
                ->setCustomOptions([
                    'linkField' => 'cylinderKey',
                    'linkCrudFqcn' => KeyCrudController::class,
                ]);
        }
    }
}
