<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Notification;

use App\Entity\Person;
use App\Entity\ThemeAffiliation;
use App\Entity\WorkflowNotification;
use App\Service\HistoricityManagerAware;
use App\Settings\SettingManager;
use App\Workflow\Approval\ApprovalStrategy;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;
use Twig\Environment;

class NotificationDispatcher implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    //    {
    //        getSubscribedServices as traitSubscribedServices;
    //    }
    use HistoricityManagerAware;

    private readonly ServiceLocator $approvalLocator;

    public function __construct(
        #[TaggedLocator(ApprovalStrategy::class)]
        ServiceLocator $approvalLocator
    ) {
        $this->approvalLocator = $approvalLocator;
    }

    /**
     * Sends the given notification about the given subject
     *
     * @param WorkflowNotification $notification
     * @param Person $subject the subject of the notification, *not* the recipient or sender
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendNotification(WorkflowNotification $notification, Person $subject): void
    {
        // Render the notification
        $template = $this->twig()->createTemplate($notification->getTemplate());
        $themes = array_map(fn(ThemeAffiliation $ta) => $ta->getTheme(),
            $this->historicityManager()->getCurrentEntities($subject->getThemeAffiliations())->toArray());
        $body = $this->twig()->render($template, [
            // todo what arguments do we want to provide?
            'member' => $subject,
            'themes' => $themes,
        ]);

        // Render a base notification template with the signature
        $html = $this->twig()->render('workflow/notification/base.html.twig', [
            'body' => $body,
            'subject' => $notification->getSubject(),
        ]);

        // Render the recipients
        $recipientTemplate = $this->twig()->createTemplate($notification->getRecipients());
        $recipients = $this->twig()->render($recipientTemplate, [
            'approvers' => $this->getApprovalEmails($notification, $subject),
            // todo what recipients do we want to provide?
            'member' => $subject->getEmail(),
        ]);

        $email = (new Email())
            ->from($this->settingManager()->get('notification_from'))
            ->to($recipients)
            ->subject($notification->getSubject())
            ->html($html);

        // Send the notification to each recipient
        $this->mailer()->send($email);
    }

    /**
     * @param WorkflowNotification $notification
     * @param Person $subject
     * @return string
     */
    protected function getApprovalEmails(WorkflowNotification $notification, Person $subject): string
    {
        // get the workflow transition, get the metadata, hydrate the ApprovalStrategy, get the approvers, make a list of their emails
        $approvalEmails = '';
        $membershipStateMachine = $this->membershipStateMachine();
        /** @var Transition $transition */
        $transition = current(
            array_filter(
                $membershipStateMachine->getDefinition()->getTransitions(),
                function (Transition $transition) use ($notification) {
                    return $transition->getName() === $notification->getTransitionName();
                }
            )
        );
        // We can trust that this is a state machine, and thus only has one "to"
        $approvalStrategyClass = $membershipStateMachine->getMetadataStore()->getMetadata(
            'approvalStrategy',
            $transition->getTos()[0]
        );
        if ($approvalStrategyClass
            && class_exists($approvalStrategyClass)
            && in_array(ApprovalStrategy::class, class_implements($approvalStrategyClass))) {
            /** @var ApprovalStrategy $approvalStrategy */
            $approvalStrategy = $this->approvalLocator->get($approvalStrategyClass);
            $approvalEmails = join(
                ",",
                $approvalStrategy->getApprovalEmails($subject)
            );
        }

        return $approvalEmails;
    }

    //MARK: - Service Subscribers

    #[SubscribedService]
    private function twig(): Environment
    {
        return $this->container->get(__CLASS__.'::'.__FUNCTION__);
    }

    #[SubscribedService]
    private function mailer(): MailerInterface
    {
        return $this->container->get(__CLASS__.'::'.__FUNCTION__);
    }

    #[SubscribedService(attributes: new Autowire(service: 'state_machine.membership'))]
    private function membershipStateMachine(): WorkflowInterface
    {
        return $this->container->get(__CLASS__.'::'.__FUNCTION__);
    }

    #[SubscribedService]
    private function settingManager(): SettingManager
    {
        return $this->container->get(__CLASS__.'::'.__FUNCTION__);
    }
}