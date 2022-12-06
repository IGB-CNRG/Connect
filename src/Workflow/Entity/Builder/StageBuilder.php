<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Entity\Builder;

use App\Repository\PersonRepository;
use App\Workflow\Approval\ReceptionApproval;
use App\Workflow\Approval\ThemeApproval;
use App\Workflow\Entity\Stage;
use App\Workflow\Validation\EmptyValidation;
use Exception;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

/**
 * A Builder for Stages. This should only need to be used from within WorkflowFactory; individual Stages are not useful
 *   outside the context of a Workflow.
 */
class StageBuilder implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    private string $name;
    private string $approvalType;
    private string $validationType;

    /**
     * Builds the stage defined by this builder. Resets the builder for re-use.
     * @return Stage
     * @throws Exception
     */
    public function buildStage(): Stage
    {
        $approvalStrategy = match ($this->approvalType) {
            'theme' => new ThemeApproval($this->personRepository()),
            'reception' => new ReceptionApproval($this->personRepository()),
            default => throw new Exception("Unknown approval strategy"),
        };

        $validationStrategy = match ($this->validationType) {
            'none' => new EmptyValidation(),
            default => throw new Exception("Unknown validation strategy"),
        };

        $stage = new Stage($this->name, $approvalStrategy, $validationStrategy);

        $this->name = '';
        $this->approvalType = '';
        $this->validationType = '';

        return $stage;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $approvalType
     * @return $this
     */
    public function setApprovalType(string $approvalType): self
    {
        $this->approvalType = $approvalType;
        return $this;
    }

    /**
     * @param string $validationType
     * @return StageBuilder
     */
    public function setValidationType(string $validationType): self
    {
        $this->validationType = $validationType;
        return $this;
    }

    // MARK: - Service Subscribers
    #[SubscribedService]
    private function personRepository(): PersonRepository
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}