<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity\Workflow;

use App\Entity\Person;
use App\Enum\Workflow\ClearApproversTrait;
use App\Enum\Workflow\PersonEntryStage;
use App\Enum\Workflow\WorkflowStage;
use App\Repository\PersonEntryWorkflowProgressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonEntryWorkflowProgressRepository::class)]
class PersonEntryWorkflowProgress implements PersonWorkflowProgress
{
    use ClearApproversTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true, enumType: PersonEntryStage::class)]
    private ?PersonEntryStage $stage = null;

    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'personEntryWorkflowApprovals')]
    private Collection $approvers;

    #[ORM\OneToOne(inversedBy: 'personEntryWorkflowProgress', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    public function __construct()
    {
        $this->approvers = new ArrayCollection();
    }

    public function getProgress(){
        $total = count($this->getStage()::cases())*2;
        $current = $this->getStage()->position()*2;
        if($this->isWaitingForApproval()){
            $current+=1;
        }
        return round($current/$total*100.0);
    }

    public function isWaitingForApproval(): bool
    {
        return $this->getApprovers()->count()>0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStage(): ?PersonEntryStage
    {
        return $this->stage;
    }

    /**
     * @param PersonEntryStage|WorkflowStage|null $stage
     * @return $this
     */
    public function setStage(PersonEntryStage|null|WorkflowStage $stage): self
    {
        $this->stage = $stage;

        return $this;
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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        $this->person = $person;

        return $this;
    }
}
