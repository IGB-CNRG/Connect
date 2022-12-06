<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Entity\Factory;

use App\Workflow\Entity\Builder\StageBuilder;
use App\Workflow\Entity\Workflow;
use Exception;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class WorkflowFactory implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    private array $workflowCache = [];

    private function createEntryWorkflow(): Workflow
    {
        $stages = [
            $this->stageBuilder()
                ->setName('submit_form')
                ->setApprovalType('theme')
                ->setValidationType('none')
                ->buildStage(),
            $this->stageBuilder()
                ->setName('upload_certs')
                ->setApprovalType('reception')
                ->setValidationType('none')
                ->buildStage(),
        ];
        return new Workflow("entry", $stages);
    }

    public function createWorkflowByName(string $name): Workflow
    {
        // Create and cache the Workflow, if it's not in the cache already
        if (!key_exists($name, $this->workflowCache)) {
            $this->workflowCache[$name] = match ($name) {
                'entry' => $this->createEntryWorkflow(),
                default => throw new Exception('Invalid workflow name'),
            };
        }
        return $this->workflowCache[$name];
    }

    //MARK: - Service Subscribers
    #[SubscribedService]
    private function stageBuilder(): StageBuilder
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}