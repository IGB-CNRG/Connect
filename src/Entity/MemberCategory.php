<?php

namespace App\Entity;

use App\Repository\MemberCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberCategoryRepository::class)]
class MemberCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'memberCategory', targetEntity: ThemeAffiliation::class, orphanRemoval: true)]
    private $themeAffiliations;

    #[ORM\ManyToMany(targetEntity: WorkflowStep::class, mappedBy: 'memberCategories')]
    private $workflowSteps;

    #[ORM\OneToMany(mappedBy: 'memberCategory', targetEntity: WorkflowStepCategory::class, orphanRemoval: true)]
    private $workflowStepCategories;

    public function __construct()
    {
        $this->themeAffiliations = new ArrayCollection();
        $this->workflowSteps = new ArrayCollection();
        $this->workflowStepCategories = new ArrayCollection();
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
     * @return Collection<int, ThemeAffiliation>
     */
    public function getThemeAffiliations(): Collection
    {
        return $this->themeAffiliations;
    }

    public function addThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if (!$this->themeAffiliations->contains($themeAffiliation)) {
            $this->themeAffiliations[] = $themeAffiliation;
            $themeAffiliation->setMemberCategory($this);
        }

        return $this;
    }

    public function removeThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if ($this->themeAffiliations->removeElement($themeAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($themeAffiliation->getMemberCategory() === $this) {
                $themeAffiliation->setMemberCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkflowStep>
     */
    public function getWorkflowSteps(): Collection
    {
        return $this->workflowSteps;
    }

    public function addWorkflowStep(WorkflowStep $workflowStep): self
    {
        if (!$this->workflowSteps->contains($workflowStep)) {
            $this->workflowSteps[] = $workflowStep;
            $workflowStep->addMemberCategory($this);
        }

        return $this;
    }

    public function removeWorkflowStep(WorkflowStep $workflowStep): self
    {
        if ($this->workflowSteps->removeElement($workflowStep)) {
            $workflowStep->removeMemberCategory($this);
        }

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
            $workflowStepCategory->setMemberCategory($this);
        }

        return $this;
    }

    public function removeWorkflowStepCategory(WorkflowStepCategory $workflowStepCategory): self
    {
        if ($this->workflowStepCategories->removeElement($workflowStepCategory)) {
            // set the owning side to null (unless already changed)
            if ($workflowStepCategory->getMemberCategory() === $this) {
                $workflowStepCategory->setMemberCategory(null);
            }
        }

        return $this;
    }
}
