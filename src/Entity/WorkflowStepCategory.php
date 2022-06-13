<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\WorkflowStepCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: WorkflowStepCategoryRepository::class)]
class WorkflowStepCategory
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: WorkflowStep::class, inversedBy: 'workflowStepCategories')]
    #[ORM\JoinColumn(nullable: false)]
    private $workflowStep;

    #[ORM\ManyToOne(targetEntity: MemberCategory::class, inversedBy: 'workflowStepCategories')]
    #[ORM\JoinColumn(nullable: false)]
    private $memberCategory;

    #[ORM\Column(type: 'integer')]
    private $position;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkflowStep(): ?WorkflowStep
    {
        return $this->workflowStep;
    }

    public function setWorkflowStep(?WorkflowStep $workflowStep): self
    {
        $this->workflowStep = $workflowStep;

        return $this;
    }

    public function getMemberCategory(): ?MemberCategory
    {
        return $this->memberCategory;
    }

    public function setMemberCategory(?MemberCategory $memberCategory): self
    {
        $this->memberCategory = $memberCategory;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
