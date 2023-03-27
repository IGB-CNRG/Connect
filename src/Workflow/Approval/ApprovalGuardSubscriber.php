<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Repository\PersonRepository;
use App\Service\HistoricityManagerAware;
use App\Service\SecurityAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\WorkflowEvents;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ApprovalGuardSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, SecurityAware, HistoricityManagerAware;

    public function approvalGuard(GuardEvent $event)
    {
        if($this->security()->isGranted('ROLE_ADMIN')){
            // an admin should always be able to approve
            return;
        }
        $approvalStrategyClass = $event->getMetadata('approvalStrategy', $event->getTransition());
        if ($approvalStrategyClass
            && class_exists($approvalStrategyClass)
            && in_array(ApprovalStrategy::class, class_implements($approvalStrategyClass))) {
            /** @var ApprovalStrategy $approvalStrategy */
            $approvalStrategy = new $approvalStrategyClass($this->personRepository(), $this->historicityManager());
            // todo could we get the strategy from the container so we can support autowiring?
            $approvers = $approvalStrategy->getApprovers($event->getSubject());
            if(!in_array($this->security()->getUser(), $approvers)){
                $event->setBlocked(true, "You are not authorized to approve this form.");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkflowEvents::GUARD => "approvalGuard"
        ];
    }

    #[SubscribedService]
    private function personRepository(): PersonRepository
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}