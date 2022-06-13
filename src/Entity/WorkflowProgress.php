<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\WorkflowProgressRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: WorkflowProgressRepository::class)]
class WorkflowProgress
{
    use TimestampableEntity, HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'workflowProgress')]
    #[ORM\JoinColumn(nullable: false)]
    private Person $person;

    #[ORM\ManyToOne(targetEntity: WorkflowStep::class, inversedBy: 'workflowProgress')]
    #[ORM\JoinColumn(nullable: false)]
    private WorkflowStep $workflowStep;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getWorkflowStep(): WorkflowStep
    {
        return $this->workflowStep;
    }

    public function setWorkflowStep(WorkflowStep $workflowStep): self
    {
        $this->workflowStep = $workflowStep;

        return $this;
    }
}
