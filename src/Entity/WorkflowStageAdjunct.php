<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Enum\PersonEntryStage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait WorkflowStageAdjunct
{
    #[ORM\ManyToMany(targetEntity: MemberCategory::class)]
    private Collection $memberCategories;

    #[ORM\Column(enumType: PersonEntryStage::class)]
    private array $personEntryStage = [];

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

    /**
     * @return PersonEntryStage[]
     */
    public function getPersonEntryStage(): array
    {
        return $this->personEntryStage;
    }

    public function setPersonEntryStage(array $personEntryStage): self
    {
        $this->personEntryStage = $personEntryStage;

        return $this;
    }
}