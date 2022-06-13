<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\MemberCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
            ->setEntityLabelInPlural('Member Categories')//            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('shortName'),
        ];
    }
}
