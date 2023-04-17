<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Unit;
use App\Entity\UnitAffiliation;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use LogicException;

class OtherUnitAffiliationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UnitAffiliation::class;
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
            ->andWhere('entity.otherUnit is not null');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, '"Other" Units')
            ->overrideTemplate('crud/edit', 'admin/edit_unit.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $resolveUnitAction = Action::new('resolve')
            ->linkToCrudAction('resolve');
        return parent::configureActions($actions)
            ->disable(Action::DELETE, Action::NEW)
//            ->add(Crud::PAGE_INDEX, $resolveUnitAction)
            ;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addWebpackEncoreEntry('admin');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('person')->hideOnForm(),
            AssociationField::new('unit')->onlyOnForms()->setQueryBuilder(
                fn(QueryBuilder $queryBuilder) => $queryBuilder->getEntityManager()
                    ->getRepository(Unit::class)
                    ->createFormSortedQueryBuilder()
            )->setFormTypeOptions([
                'attr' => [
                    'data-other-entry-target' => 'select',
                    'data-action' => 'change->other-entry#toggle',
                ],
                'placeholder' => 'Other (please specify)',
                'group_by' => function (Unit $choice, $key, $value) {
                    if ($choice->getParentUnit()) {
                        return $choice->getParentUnit();
                    } else {
                        return null;
                    }
                },
            ]),
            TextField::new('otherUnit')->setFormTypeOptions([
                'attr' => [
                    'data-other-entry-target' => 'other',
                ],
            ]),
        ];
    }


    public function resolve(AdminContext $adminContext)
    {
        $unitAffiliation = $adminContext->getEntity()->getInstance();
        if (!$unitAffiliation instanceof UnitAffiliation) {
            throw new LogicException('Entity is missing or not a UnitAffiliation');
        }
    }
}
