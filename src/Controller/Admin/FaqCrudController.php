<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Faq;
use App\Repository\FaqRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FaqCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Faq::class;
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
            ->addOrderBy('entity.position');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('FAQ')
            ->setEntityLabelInPlural('FAQs')
            ->overrideTemplate('crud/edit', 'admin/edit_unit.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('question'),
            TextEditorField::new('answer'),
        ];
    }

    public function createEntity(string $entityFqcn)
    {
        $faq = new Faq();
        /** @var FaqRepository $faqRepository */
        $faqRepository = ($this->container)(FaqRepository::class);
        $maxPosition = $faqRepository->getHighestPosition();
        if(!$maxPosition){
            $position = 0;
        } else {
            $position = $maxPosition+1;
        }
        $faq->setPosition($position);

        return $faq;
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(),[
            FaqRepository::class,
        ]);
    }
}
