<?php

namespace App\Entity;

use App\Repository\WorkflowProgressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkflowProgressRepository::class)]
class WorkflowProgress
{
    use HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'workflowProgress')]
    #[ORM\JoinColumn(nullable: false)]
    private $person;

    #[ORM\ManyToOne(targetEntity: WorkflowStep::class, inversedBy: 'workflowProgress')]
    #[ORM\JoinColumn(nullable: false)]
    private $workflowStep;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
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
}
