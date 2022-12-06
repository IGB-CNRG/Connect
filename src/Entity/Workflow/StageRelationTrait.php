<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity\Workflow;

use App\Workflow\Entity\Factory\WorkflowFactory;
use App\Workflow\Entity\Stage;
use App\Workflow\Entity\Workflow;
use Doctrine\ORM\Mapping as ORM;
use Exception;

trait StageRelationTrait
{
    #[ORM\Column(length: 255)]
    private ?string $workflowName = null;

    #[ORM\Column(length: 255)]
    private ?string $stageName = null;

    //MARK: - Getters/Setters
    public function getWorkflowName(): ?string
    {
        return $this->workflowName;
    }

    public function setWorkflowName(string $workflowName): self
    {
        $this->workflowName = $workflowName;

        return $this;
    }

    public function getStageName(): ?string
    {
        return $this->stageName;
    }

    public function setStageName(string $stageName): self
    {
        $this->stageName = $stageName;

        return $this;
    }

    // MARK: - Stage/Workflow Helpers
    public function getStage(WorkflowFactory $workflowFactory): Stage
    {
        $workflow = $workflowFactory->createWorkflowByName($this->workflowName);
        return $workflow->getStageByName($this->stageName);
    }

    public function setStage(Stage $stage): self
    {
        if ($this->getWorkflowName() === null) {
            $this->setStageName($stage->getName())->setWorkflowName($stage->getWorkflow()->getName());
        } elseif ($this->getWorkflowName() === $stage->getWorkflow()->getName()) {
            $this->setStageName($stage->getName());
        } else {
            throw new Exception("Cannot move workflow progress to a different workflow");
        }
        return $this;
    }

    public function getWorkflow(WorkflowFactory $workflowFactory): Workflow
    {
        return $workflowFactory->createWorkflowByName($this->workflowName);
    }

    public function setWorkflow(Workflow $workflow): self
    {
        if ($this->workflowName === null) {
            $this->workflowName = $workflow->getName();
            $this->stageName = $workflow->firstStage()->getName();
        } else {
            throw new Exception("Cannot move workflow progress to a different workflow");
        }
        return $this;
    }
}