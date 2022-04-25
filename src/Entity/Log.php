<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\LogRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'ownedLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 255)]
    private $text;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'logs')]
    private $person;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'logs')]
    private $theme;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'logs')]
    private $room;

    #[ORM\ManyToOne(targetEntity: Key::class, inversedBy: 'logs')]
    private $cylinderKey;

    #[ORM\ManyToOne(targetEntity: Workflow::class, inversedBy: 'logs')]
    private $workflow;

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'logs')]
    private $department;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Person
    {
        return $this->user;
    }

    public function setUser(?Person $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
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

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getCylinderKey(): ?Key
    {
        return $this->cylinderKey;
    }

    public function setCylinderKey(?Key $cylinderKey): self
    {
        $this->cylinderKey = $cylinderKey;

        return $this;
    }

    public function getWorkflow(): ?Workflow
    {
        return $this->workflow;
    }

    public function setWorkflow(?Workflow $workflow): self
    {
        $this->workflow = $workflow;

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
}
