<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\LogSubjectInterface;
use App\Repository\UnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UnitRepository::class)]
class Unit implements LogSubjectInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['log:person'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'unit', targetEntity: Log::class)]
    private Collection $logs;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shortName = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childUnits')]
    private ?self $parentUnit = null;

    #[ORM\OneToMany(mappedBy: 'parentUnit', targetEntity: self::class)]
    private Collection $childUnits;

    #[ORM\OneToMany(mappedBy: 'unit', targetEntity: Person::class)]
    private Collection $people;

    public function __construct()
    {
        $this->logs = new ArrayCollection();
        $this->childUnits = new ArrayCollection();
        $this->people = new ArrayCollection();
    }

    public function __toString()
    {
        if ($this->getShortName()) {
            return "{$this->getName()} ({$this->getShortName()})";
        } else {
            return $this->getName();
        }
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
            $log->setUnit($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getUnit() === $this) {
                $log->setUnit(null);
            }
        }

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getParentUnit(): ?self
    {
        return $this->parentUnit;
    }

    public function setParentUnit(?self $parentUnit): self
    {
        $this->parentUnit = $parentUnit;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildUnits(): Collection
    {
        return $this->childUnits;
    }

    public function addChildUnit(self $childUnit): self
    {
        if (!$this->childUnits->contains($childUnit)) {
            $this->childUnits->add($childUnit);
            $childUnit->setParentUnit($this);
        }

        return $this;
    }

    public function removeChildUnit(self $childUnit): self
    {
        if ($this->childUnits->removeElement($childUnit)) {
            // set the owning side to null (unless already changed)
            if ($childUnit->getParentUnit() === $this) {
                $childUnit->setParentUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
            $person->setUnit($this);
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->people->removeElement($person)) {
            // set the owning side to null (unless already changed)
            if ($person->getUnit() === $this) {
                $person->setUnit(null);
            }
        }

        return $this;
    }
}
