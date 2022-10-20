<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity\Workflow;

use App\Entity\MemberCategory;
use App\Enum\Workflow\PersonEntryStage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait WorkflowStageAdjunct
{
    #[ORM\ManyToMany(targetEntity: MemberCategory::class)]
    private Collection $memberCategories;

    #[ORM\Column(enumType: PersonEntryStage::class)]
    private ?PersonEntryStage $personEntryStage = null;

    private function adjunctConstruct(): void
    {
        $this->memberCategories = new ArrayCollection();
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
            $this->memberCategories->add($memberCategory);
        }

        return $this;
    }

    public function removeMemberCategory(MemberCategory $memberCategory): self
    {
        $this->memberCategories->removeElement($memberCategory);

        return $this;
    }

    public function getPersonEntryStage(): ?PersonEntryStage
    {
        return $this->personEntryStage;
    }

    public function setPersonEntryStage(?PersonEntryStage $personEntryStage): self
    {
        $this->personEntryStage = $personEntryStage;

        return $this;
    }
}