<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Entity;

use Exception;

/**
 * Represents an entire workflow.
 *
 * This is *not* a Doctrine Entity, as the basic layout of a workflow is not user-definable.
 */
class Workflow
{
    /**
     * @param string $name
     * @param Stage[] $stages
     */
    public function __construct(private string $name, private array $stages)
    {
        foreach ($this->stages as $stage) {
            $stage->setWorkflow($this);
        }
    }

    public function firstStage(): Stage
    {
        return $this->stages[0];
    }

    /**
     * @param string $name
     * @return Stage
     * @throws Exception
     */
    public function getStageByName(string $name): Stage
    {
        foreach ($this->stages as $stage) {
            if ($stage->getName() === $name) {
                return $stage;
            }
        }
        throw new Exception("Unknown stage name");
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
     * @return array
     */
    public function getStages(): array
    {
        return $this->stages;
    }
}