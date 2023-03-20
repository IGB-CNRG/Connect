<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Entity\WorkflowNotification;
use App\Workflow\Notification\NotificationDispatcher;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Workflow\WorkflowInterface;

class WorkflowNotificationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WorkflowNotification::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $sendTestEmail = Action::new('sendTest', 'Send test email', 'fas fa-envelope')
        ->linkToCrudAction('sendTest');
        return $actions
            ->add(Crud::PAGE_INDEX, $sendTestEmail)
            ->add(Crud::PAGE_DETAIL, $sendTestEmail)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Workflow Notifications')
            ->setEntityLabelInSingular('workflow notification')
            ->setEntityLabelInPlural('workflow notifications');
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var WorkflowInterface $workflow */
        $workflow = $this->container->get('state_machine.membership');
        $transitions = $workflow->getDefinition()->getTransitions();

        $choices = [];
        foreach ($transitions as $transition) {
            $label = 'membership.' . $transition->getName() . '.label';
            $choices[$label] = $transition->getName();
        }

        return [
            TextField::new('name')
                ->setHelp('This name is for internal use only and will not be included in the email'),
            TextField::new('subject')
                ->setLabel('Email subject'),
            CodeEditorField::new('template')
                ->setLabel('Email template')
                ->setHelp(
                    'In this template, you can use the following keywords, which will be replaced with the appropriate values when the email is sent:
                        <dl>
                        <dt>{{member.name}}</dt>
                        <dd>The member\'s full name (e.g., "John Smith")</dd>
                        <dt>{{member.firstName}}</dt>
                        <dd>The member\'s first name (e.g., "John")</dd>
                        <dt>{{member.lastName}}</dt>
                        <dd>The member\'s last name (e.g., "Smith")</dd>
                        <dt>{{member.username}}</dt>
                        <dd>The member\'s IGB username (e.g., "jsmith")</dd>
                        </dl>'
                )
                ->setFormTypeOption('help_html', true),
            TextField::new('recipients')
                ->setHelp(
                    'Enter email addresses, separated by commas. You can also use the following keywords:
                        <dl>
                        <dt>{{member}}</dt>
                        <dd>The IGB member</dd>
                        <dt>{{approvers}}</dt>
                        <dd>The list of people who can approve this workflow step</dd>
                        </dl>'
                )
                ->setFormTypeOption('help_html', true),
            AssociationField::new('memberCategories')
            ->setTemplatePath('admin/member_category.html.twig'),
            ChoiceField::new('transitionName')
                ->setLabel('Workflow event')
                ->setChoices($choices)
                ->setFormTypeOption('choice_translation_domain', true),
            BooleanField::new('isEnabled')->renderAsSwitch(false),

        ];
    }

    public function createEntity(string $entityFqcn)
    {
        return (new WorkflowNotification())
            ->setWorkflowName('membership');
    }

    public function sendTest(AdminContext $context): RedirectResponse
    {
        /** @var WorkflowNotification $notification */
        $notification = $context->getEntity()->getInstance();

        // Override recipients to the current user
        /** @var Person $user */
        $user = $this->getUser();
        $notification->setRecipients($user->getEmail());

        /** @var NotificationDispatcher $notificationDispatcher */
        $notificationDispatcher = $this->container->get(NotificationDispatcher::class);
        $notificationDispatcher->sendNotification($notification, $user);

        return $this->redirect($context->getReferrer());
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'state_machine.membership' => WorkflowInterface::class,
            NotificationDispatcher::class => NotificationDispatcher::class,
        ]);
    }
}
