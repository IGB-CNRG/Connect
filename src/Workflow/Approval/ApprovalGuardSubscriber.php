<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Service\HistoricityManagerAware;
use App\Service\SecurityAware;
use App\Workflow\Membership;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\WorkflowEvents;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ApprovalGuardSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface
{
    use HistoricityManagerAware;
    use SecurityAware;
    use ServiceSubscriberTrait;

    public function approvalGuard(GuardEvent $event): void
    {
        if ($this->security()->isGranted('ROLE_ADMIN')) {
            // an admin should always be able to approve
            return;
        }
        $approvers = $this->membership()->getApprovers($event->getSubject(), $event->getTransition());
        if ($approvers !== null && !in_array($this->security()->getUser(), $approvers)) {
            $event->setBlocked(true, "You are not authorized to approve this form.");
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkflowEvents::GUARD => "approvalGuard",
        ];
    }

    #[SubscribedService]
    private function membership(): Membership
    {
        return $this->container->get(__CLASS__.'::'.__FUNCTION__);
    }
}