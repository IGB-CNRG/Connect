<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\ExitReason;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExitReasonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ExitReason::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Exit Reason')
            ->setEntityLabelInPlural('Exit Reasons');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
        ];
    }
}
