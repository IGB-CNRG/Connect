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
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['log:person'])]
    private ?string $name;

    #[ORM\OneToMany(mappedBy: 'unit', targetEntity: UnitAffiliation::class, orphanRemoval: true)]
    private Collection $unitAffiliations;

    #[ORM\OneToMany(mappedBy: 'unit', targetEntity: Log::class)]
    private Collection $logs;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shortName = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childUnits')]
    private ?self $parentUnit = null;

    #[ORM\OneToMany(mappedBy: 'parentUnit', targetEntity: self::class)]
    private Collection $childUnits;

    public function __construct()
    {
        $this->unitAffiliations = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->childUnits = new ArrayCollection();
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
     * @return Collection<int, UnitAffiliation>
     */
    public function getUnitAffiliations(): Collection
    {
        return $this->unitAffiliations;
    }

    public function addUnitAffiliation(UnitAffiliation $unitAffiliation): self
    {
        if (!$this->unitAffiliations->contains($unitAffiliation)) {
            $this->unitAffiliations[] = $unitAffiliation;
            $unitAffiliation->setUnit($this);
        }

        return $this;
    }

    public function removeUnitAffiliation(UnitAffiliation $unitAffiliation): self
    {
        if ($this->unitAffiliations->removeElement($unitAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($unitAffiliation->getUnit() === $this) {
                $unitAffiliation->setUnit(null);
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
}
