<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Notification;

use App\Entity\Person;
use App\Repository\WorkflowNotificationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class NotificationSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    /**
     * Sends all registered notifications pertaining to the given workflow transition
     *
     * @param Event $event
     * @return void
     */
    public function sendTransitionNotifications(Event $event): void
    {
        // Fetch all the notifications for this transition from the database
        /** @var Person $subject */
        $subject = $event->getSubject();
        $notifications = $this->workflowNotificationRepository()->findForTransition(
            $event->getWorkflowName(),
            $event->getTransition()->getName(),
            $subject->getMemberCategories()
        );
        // Send them
        foreach ($notifications as $notification) {
            $this->notificationDispatcher()->sendNotification($notification, $subject);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.completed' => 'sendTransitionNotifications',
        ];
    }

    //MARK: - Service Subscribers
    #[SubscribedService]
    private function workflowNotificationRepository(): WorkflowNotificationRepository
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }

    #[SubscribedService]
    private function notificationDispatcher(): NotificationDispatcher
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}