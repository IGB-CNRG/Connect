<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Service;

use App\Entity\Workflow\WorkflowProgress;
use App\Repository\WorkflowNotificationRepository;
use App\Service\EntityManagerAware;
use App\Workflow\Entity\Factory\WorkflowFactory;
use App\Workflow\Entity\Stage;
use App\Workflow\Enum\WorkflowEvent;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

/**
 * Handles the lifecycle of a workflow
 */
class WorkflowManager implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, EntityManagerAware;

    public function submitForApproval(WorkflowProgress $progress)
    {
        $stage = $progress->getStage($this->workflowFactory());
        if($stage->isValid($progress)) {// todo should we validate here? or somewhere else?
            $progress->clearApprovers();
            $approvers = $stage->getApprovalStrategy()->getApprovers($progress);
            foreach ($approvers as $approver) {
                $progress->addApprover($approver);
                $approver->setRoles(
                    array_merge($approver->getRoles(), ['ROLE_APPROVER'])
                ); // Give them the approver role
            }
            // todo there should probably be a flag that this is ready for approval

            $this->dispatch(WorkflowEvent::BeforeApproval, $stage, $progress);
        }
    }

    public function disapprove(WorkflowProgress $progress)
    {
        // todo stub
    }

    public function approve(WorkflowProgress $progress)
    {
        // todo stub
        $stage = $progress->getStage($this->workflowFactory());

        $this->dispatch(WorkflowEvent::AfterApproval, $stage, $progress);
    }

    private function dispatch(WorkflowEvent $event, Stage $stage, WorkflowProgress $progress)
    {
        $notifications = $this->workflowNotificationRepository()->findByStage(
            $stage,
            $progress->getMemberCategory(),
            $event
        );
        foreach ($notifications as $notification) {
            $this->sendNotification($notification, $progress);
        }
    }

    private function sendNotification(mixed $notification, WorkflowProgress $progress)
    {
        // todo stub
    }


    #[SubscribedService]
    private function workflowFactory(): WorkflowFactory
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }

    #[SubscribedService]
    private function workflowNotificationRepository(): WorkflowNotificationRepository
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}