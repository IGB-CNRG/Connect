<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Notification;

use App\Entity\Person;
use App\Entity\ThemeAffiliation;
use App\Entity\WorkflowNotification;
use App\Repository\PersonRepository;
use App\Service\HistoricityManagerAware;
use App\Workflow\Approval\ApprovalStrategy;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
    use ServiceSubscriberTrait {
        getSubscribedServices as traitSubscribedServices;
    }
    use HistoricityManagerAware;

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

        // Render the recipients
        $recipientTemplate = $this->twig()->createTemplate($notification->getRecipients());
        $recipients = $this->twig()->render($recipientTemplate, [
            'approvers' => $this->getApprovalEmails($notification, $subject),
            // todo what recipients do we want to provide?
            'member' => $subject->getEmail(),
        ]);

        $email = (new Email())
            ->from('do-not-reply@igb.illinois.edu') // todo parameterize this and figure out what it should be
            ->to($recipients)
            ->subject($notification->getSubject())
            ->html($body);

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
        /** @var WorkflowInterface $membershipStateMachine */
        try {
            $membershipStateMachine = $this->container->get('state_machine.' . $notification->getWorkflowName());
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return $approvalEmails;
        }
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
            $approvalStrategy = new $approvalStrategyClass($this->personRepository());
            $approvers = $approvalStrategy->getApprovers($subject);
            $approvalEmails = join(
                ",",
                array_map(function (Person $approver) {
                    return $approver->getEmail();
                }, $approvers)
            );
        }
        return $approvalEmails;
    }

    //MARK: - Service Subscribers

    #[SubscribedService]
    private function twig(): Environment
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }

    #[SubscribedService]
    private function mailer(): MailerInterface
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }

    #[SubscribedService]
    private function personRepository(): PersonRepository
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(static::traitSubscribedServices(), ['state_machine.membership' => WorkflowInterface::class]);
    }

}