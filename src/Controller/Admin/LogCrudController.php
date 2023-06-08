<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Log;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Log::class;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        return parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        )
            ->addCriteria(Criteria::create()->andWhere(Criteria::expr()->neq('person', null)))
            ->addOrderBy('entity.createdAt', 'desc');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::EDIT, Action::NEW, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('createdAt')->setLabel('Log Time'),
            AssociationField::new('user')->setLabel('Editor')->setHelp('The person who performed the action'),
            AssociationField::new('person')->setLabel('Subject')->setHelp('The person the action was performed on'),
            TextField::new('text'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('user')
            ->add('person')
            ->add('createdAt');
    }

}
