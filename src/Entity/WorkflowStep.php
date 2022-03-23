<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\WorkflowStepRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkflowStepRepository::class)]
class WorkflowStep
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'workflowStep', targetEntity: WorkflowStepCategory::class, orphanRemoval: true)]
    private $workflowStepCategories;

    #[ORM\OneToMany(mappedBy: 'workflowStep', targetEntity: WorkflowProgress::class, orphanRemoval: true)]
    private $workflowProgress;

    public function __construct()
    {
        $this->memberCategories = new ArrayCollection();
        $this->workflowStepCategories = new ArrayCollection();
        $this->workflowProgress = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, MemberCategory>
     */
    public function getMemberCategories(): Collection
    {
        return $this->memberCategories;
    }

    public function addMemberCategory(MemberCategory $memberCategory): self
    {
        if (!$this->memberCategories->contains($memberCategory)) {
            $this->memberCategories[] = $memberCategory;
        }

        return $this;
    }

    public function removeMemberCategory(MemberCategory $memberCategory): self
    {
        $this->memberCategories->removeElement($memberCategory);

        return $this;
    }

    /**
     * @return Collection<int, WorkflowStepCategory>
     */
    public function getWorkflowStepCategories(): Collection
    {
        return $this->workflowStepCategories;
    }

    public function addWorkflowStepCategory(WorkflowStepCategory $workflowStepCategory): self
    {
        if (!$this->workflowStepCategories->contains($workflowStepCategory)) {
            $this->workflowStepCategories[] = $workflowStepCategory;
            $workflowStepCategory->setWorkflowStep($this);
        }

        return $this;
    }

    public function removeWorkflowStepCategory(WorkflowStepCategory $workflowStepCategory): self
    {
        if ($this->workflowStepCategories->removeElement($workflowStepCategory)) {
            // set the owning side to null (unless already changed)
            if ($workflowStepCategory->getWorkflowStep() === $this) {
                $workflowStepCategory->setWorkflowStep(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkflowProgress>
     */
    public function getWorkflowProgress(): Collection
    {
        return $this->workflowProgress;
    }

    public function addWorkflowProgress(WorkflowProgress $workflowProgress): self
    {
        if (!$this->workflowProgress->contains($workflowProgress)) {
            $this->workflowProgress[] = $workflowProgress;
            $workflowProgress->setWorkflowStep($this);
        }

        return $this;
    }

    public function removeWorkflowProgress(WorkflowProgress $workflowProgress): self
    {
        if ($this->workflowProgress->removeElement($workflowProgress)) {
            // set the owning side to null (unless already changed)
            if ($workflowProgress->getWorkflowStep() === $this) {
                $workflowProgress->setWorkflowStep(null);
            }
        }

        return $this;
    }
}
