<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\ManyToOne(targetEntity: College::class, inversedBy: 'departments')]
    private $college;

    #[ORM\OneToMany(mappedBy: 'department', targetEntity: DepartmentAffiliation::class, orphanRemoval: true)]
    private $departmentAffiliations;

    public function __construct()
    {
        $this->departmentAffiliations = new ArrayCollection();
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
}
