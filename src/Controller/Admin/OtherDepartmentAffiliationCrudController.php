<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Department;
use App\Entity\DepartmentAffiliation;
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

class OtherDepartmentAffiliationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DepartmentAffiliation::class;
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
            ->andWhere('entity.otherDepartment is not null');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, '"Other" Departments')
            ->overrideTemplate('crud/edit', 'admin/edit_department.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $resolveDepartmentAction = Action::new('resolve')
            ->linkToCrudAction('resolve');
        return parent::configureActions($actions)
            ->disable(Action::DELETE, Action::NEW)
            ->add(Crud::PAGE_INDEX, $resolveDepartmentAction);
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
            AssociationField::new('department')->onlyOnForms()->setQueryBuilder(
                fn(QueryBuilder $queryBuilder) => $queryBuilder->getEntityManager()
                    ->getRepository(Department::class)
                    ->createFormSortedQueryBuilder()
            )->setFormTypeOptions([
                'attr' => [
                    'data-department-target' => 'select',
                    'data-action' => 'change->department#toggle',
                ],
                'placeholder' => 'Other (please specify)',
                'group_by' => function (Department $choice, $key, $value) {
                    if ($choice->getCollege()) {
                        return $choice->getCollege();
                    } else {
                        return null;
                    }
                },
            ]),
            TextField::new('otherDepartment')->setFormTypeOptions([
                'attr' => [
                    'data-department-target' => 'other',
                ],
            ]),
        ];
    }


    public function resolve(AdminContext $adminContext)
    {
        $departmentAffiliation = $adminContext->getEntity()->getInstance();
        if (!$departmentAffiliation instanceof DepartmentAffiliation) {
            throw new \LogicException('Entity is missing or not a DepartmentAffiliation');
        }
    }
}
