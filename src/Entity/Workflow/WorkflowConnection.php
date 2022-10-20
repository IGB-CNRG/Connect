<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity\Workflow;

use App\Repository\WorkflowConnectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkflowConnectionRepository::class)]
class WorkflowConnection
{
    use WorkflowStageAdjunct;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $urlTemplate = null;

    public function __construct()
    {
        $this->memberCategories = new ArrayCollection();
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

    public function getUrlTemplate(): ?string
    {
        return $this->urlTemplate;
    }

    public function setUrlTemplate(string $urlTemplate): self
    {
        $this->urlTemplate = $urlTemplate;

        return $this;
    }
}
