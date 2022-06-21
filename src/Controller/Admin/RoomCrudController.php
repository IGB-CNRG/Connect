<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Room;
use App\Repository\KeyRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RoomCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Room::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Room')
            ->setEntityLabelInPlural('Rooms')//            ->setEntityPermission('ROLE_ADMIN')
            ->setDefaultSort(['number' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('number'),
            TextField::new('name'),
            AssociationField::new('cylinderKeys')->setFormTypeOptions(
                [
                    'by_reference' => false,
                    'query_builder' => function (KeyRepository $keyRepository) {
                        return $keyRepository->createFormQueryBuilder();
                    }
                ]
            ),
            DateField::new('startedAt')->onlyOnForms(),
            DateField::new('endedAt')->onlyOnForms(),
        ];
    }
}
