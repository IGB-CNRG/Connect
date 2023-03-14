<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\Loggable;
use App\Repository\DepartmentAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DepartmentAffiliationRepository::class)]
class DepartmentAffiliation implements HistoricalEntityInterface
{
    use TimestampableEntity, HistoricalEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'departmentAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    private $person;

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'departmentAffiliations')]
    #[Loggable]
    #[Groups(['log:person'])]
    private $department;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person'])]
    private $otherDepartment;

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

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getOtherDepartment(): ?string
    {
        return $this->otherDepartment;
    }

    public function setOtherDepartment(?string $otherDepartment): self
    {
        $this->otherDepartment = $otherDepartment;

        return $this;
    }
}
