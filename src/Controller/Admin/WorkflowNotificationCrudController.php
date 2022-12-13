<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\WorkflowNotification;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Workflow\WorkflowInterface;

class WorkflowNotificationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WorkflowNotification::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Workflow Notifications')
            ->setEntityLabelInSingular('workflow notification')
            ->setEntityLabelInPlural('workflow notifications')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var WorkflowInterface $workflow */
        $workflow = $this->container->get('state_machine.membership');
        $transitions = $workflow->getDefinition()->getTransitions();

        $choices = [];
        foreach ($transitions as $transition) {
            $label = 'membership.'.$transition->getName().'.label';
            $choices[$label] = $transition->getName();
        }

        return [
            TextField::new('name'),
            TextEditorField::new('template'),
            TextField::new('recipients'),
            AssociationField::new('memberCategories'),
            ChoiceField::new('transitionName')
                ->setLabel('Workflow event')
                ->setChoices($choices)
                ->setFormTypeOption('choice_translation_domain', true)

        ];
    }

    public function createEntity(string $entityFqcn)
    {
        return (new WorkflowNotification())
            ->setWorkflowName('membership');
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'state_machine.membership' => WorkflowInterface::class,
        ]);
    }
}
