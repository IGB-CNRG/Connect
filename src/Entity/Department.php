<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\LogSubjectInterface;
use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department implements LogSubjectInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name;

    #[ORM\ManyToOne(targetEntity: College::class, inversedBy: 'departments')]
    private ?College $college;

    #[ORM\OneToMany(mappedBy: 'department', targetEntity: DepartmentAffiliation::class, orphanRemoval: true)]
    private Collection $departmentAffiliations;

    #[ORM\OneToMany(mappedBy: 'department', targetEntity: Log::class)]
    private Collection $logs;

    public function __construct()
    {
        $this->departmentAffiliations = new ArrayCollection();
        $this->logs = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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

    public function getCollege(): ?College
    {
        return $this->college;
    }

    public function setCollege(?College $college): self
    {
        $this->college = $college;

        return $this;
    }

    /**
     * @return Collection<int, DepartmentAffiliation>
     */
    public function getDepartmentAffiliations(): Collection
    {
        return $this->departmentAffiliations;
    }

    public function addDepartmentAffiliation(DepartmentAffiliation $departmentAffiliation): self
    {
        if (!$this->departmentAffiliations->contains($departmentAffiliation)) {
            $this->departmentAffiliations[] = $departmentAffiliation;
            $departmentAffiliation->setDepartment($this);
        }

        return $this;
    }

    public function removeDepartmentAffiliation(DepartmentAffiliation $departmentAffiliation): self
    {
        if ($this->departmentAffiliations->removeElement($departmentAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($departmentAffiliation->getDepartment() === $this) {
                $departmentAffiliation->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setDepartment($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getDepartment() === $this) {
                $log->setDepartment(null);
            }
        }

        return $this;
    }
}
