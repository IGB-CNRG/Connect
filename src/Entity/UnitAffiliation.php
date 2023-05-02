<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\Loggable;
use App\Log\LoggableAffiliationInterface;
use App\Repository\UnitAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UnitAffiliationRepository::class)]
class UnitAffiliation implements HistoricalEntityInterface, LoggableAffiliationInterface
{
    use HistoricalEntityTrait;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'unitAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    private ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: Unit::class, inversedBy: 'unitAffiliations')]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?Unit $unit = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person'])]
    private $otherUnit;

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

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getOtherUnit(): ?string
    {
        return $this->otherUnit;
    }

    public function setOtherUnit(?string $otherUnit): self
    {
        $this->otherUnit = $otherUnit;

        return $this;
    }

    public function getSideA()
    {
        return $this->getPerson();
    }

    public function getSideB()
    {
        return $this->getUnit();
    }

    public function getAddLogMessageA(): string
    {
        return "Added unit {$this->getUnit()}";
    }

    public function getUpdateLogMessageA(): string
    {
        return "Updated unit affiliation with {$this->getUnit()}, ";
    }

    public function getRemoveLogMessageA(): string
    {
        return "Removed unit affiliation with {$this->getUnit()}";
    }

    public function getAddLogMessageB(): string
    {
        return "Added person {$this->getPerson()}";
    }

    public function getUpdateLogMessageB(): string
    {
        return "Updated affiliation with {$this->getPerson()}, ";
    }

    public function getRemoveLogMessageB(): string
    {
        return "Removed affiliation with {$this->getPerson()}";
    }
}
