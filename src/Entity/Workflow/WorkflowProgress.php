<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity\Workflow;

use App\Entity\MemberCategory;
use App\Entity\Person;
use App\Repository\WorkflowProgressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkflowProgressRepository::class)]
class WorkflowProgress
{
    use StageRelationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'personEntryWorkflowApprovals')]
    private Collection $approvers;

    #[ORM\OneToOne(inversedBy: 'personEntryWorkflowProgress', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\ManyToOne]
    private ?MemberCategory $memberCategory = null;

    public function __construct()
    {
        $this->approvers = new ArrayCollection();
    }

    public function getProgress()
    { // todo move this to Workflow or Stage?
        $total = count($this->getStage()::cases()) * 2;
        $current = $this->getStage()->position() * 2;
        if ($this->isWaitingForApproval()) {
            $current += 1;
        }
        return round($current / $total * 100.0);
    }

    public function isWaitingForApproval(): bool
    {
        return $this->getApprovers()->count() > 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getApprovers(): Collection
    {
        return $this->approvers;
    }

    public function addApprover(Person $approver): self
    {
        if (!$this->approvers->contains($approver)) {
            $this->approvers->add($approver);
        }

        return $this;
    }

    public function removeApprover(Person $approver): self
    {
        $this->approvers->removeElement($approver);

        return $this;
    }

    public function clearApprovers(): self
    {
        foreach ($this->getApprovers() as $approver) {
            $this->removeApprover($approver);
        }
        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        $this->person = $person;

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
}
