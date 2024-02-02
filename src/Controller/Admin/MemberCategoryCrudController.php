<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\MemberCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MemberCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MemberCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Member Category')
            ->setEntityLabelInPlural('Member Categories')
            ->setDefaultSort(['name' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('shortName'),
            TextField::new('friendlyName')->setLabel('User-friendly Name')->setHelp('This name will be used on the IGB website directory'),
            BooleanField::new('canSupervise')->renderAsSwitch(false),
            BooleanField::new('needsCertificates')->renderAsSwitch(false)
        ];
    }
}
