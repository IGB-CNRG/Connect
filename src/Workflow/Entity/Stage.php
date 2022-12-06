<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Entity;

use App\Entity\Workflow\WorkflowProgress;
use App\Workflow\Approval\ApprovalStrategy;
use App\Workflow\Validation\ValidationStrategy;

/**
 * Represents a single stage of a workflow.
 *
 * This is *not* a Doctrine Entity, as the basic layout of a workflow is not user-definable.
 */
class Stage
{
    private Workflow $workflow;

    /**
     * @param string $name
     * @param ApprovalStrategy $approvalStrategy
     * @param ValidationStrategy $validationStrategy
     */
    public function __construct(
        private readonly string $name,
        private readonly ApprovalStrategy $approvalStrategy,
        private readonly ValidationStrategy $validationStrategy
    ) {}

    public function isValid(WorkflowProgress $progress): bool
    {
        return $this->validationStrategy->validate($progress);
    }

    public function position(): int
    {
        $position = array_search($this, $this->workflow->getStages());
        if(!$position){
            return 0;
        }
        return $position;
    }

    public function next(): ?Stage
    {
        $stages = $this->workflow->getStages();
        $position = array_search($this, $stages);
        if($position === false || $position === count($stages)-1){
            return null;
        }
        return $stages[$position+1];
    }

    public function prev(): ?Stage
    {
        $stages = $this->workflow->getStages();
        $position = array_search($this, $stages);
        if($position === false || $position === 0){
            return null;
        }
        return $stages[$position-1];
    }

    public function completionBeforeSubmission(): float
    {
        $total = count($this->workflow->getStages()) * 2;
        $current = $this->position() * 2;
        return round($current / $total * 100.0);
    }

    public function completionBeforeApproval(): float
    {
        $total = count($this->workflow->getStages()) * 2;
        $current = $this->position() * 2 + 1;
        return round($current / $total * 100.0);
    }

    // MARK: - Getters/Setters

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ApprovalStrategy
     */
    public function getApprovalStrategy(): ApprovalStrategy
    {
        return $this->approvalStrategy;
    }

    /**
     * @return Workflow
     */
    public function getWorkflow(): Workflow
    {
        return $this->workflow;
    }

    /**
     * @param Workflow $workflow
     */
    public function setWorkflow(Workflow $workflow): void
    {
        $this->workflow = $workflow;
    }

    /**
     * @return ValidationStrategy
     */
    public function getValidationStrategy(): ValidationStrategy
    {
        return $this->validationStrategy;
    }


}