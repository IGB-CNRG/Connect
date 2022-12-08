<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Service\SecurityAware;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ApprovalGuardSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, SecurityAware;

    public function approvalGuard(GuardEvent $event)
    {
        $approvalStrategyClass = $event->getMetadata('approvalStrategy', $event->getTransition());
        if ($approvalStrategyClass
            && class_exists($approvalStrategyClass)
            && in_array(ApprovalStrategy::class, class_implements($approvalStrategyClass))) {
            /** @var ApprovalStrategy $approvalStrategy */
            $approvalStrategy = new $approvalStrategyClass();
            $approvers = $approvalStrategy->getApprovers($event->getSubject());
            if(!in_array($this->security()->getUser(), $approvers)){ // todo an admin should always be able to approve?
                $event->setBlocked(true, "You are not authorized to approve this form.");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents(): array
    {
        return [
            "workflow.guard" => "approvalGuard"
        ];
    }
}