<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\Loggable;
use App\Log\LoggableAffiliationInterface;
use App\Repository\KeyAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: KeyAffiliationRepository::class)]
class KeyAffiliation implements HistoricalEntityInterface, LoggableAffiliationInterface
{
    use HistoricalEntityTrait;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'keyAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: Key::class, inversedBy: 'keyAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?Key $cylinderKey = null;

    public function __toString()
    {
        return $this->getCylinderKey()->__toString();
    }

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

    public function getCylinderKey(): ?Key
    {
        return $this->cylinderKey;
    }

    public function setCylinderKey(?Key $cylinderKey): self
    {
        $this->cylinderKey = $cylinderKey;

        return $this;
    }

    public function getSideA()
    {
        return $this->getPerson();
    }

    public function getSideB()
    {
        return $this->getCylinderKey();
    }

    public function getAddLogMessageA(): string
    {
        return "Added key {$this->getCylinderKey()->getName()}";
    }

    public function getUpdateLogMessageA(): string
    {
        return "Updated key assignment {$this->getCylinderKey()->getName()}, ";
    }

    public function getRemoveLogMessageA(): string
    {
        return "Removed key {$this->getCylinderKey()->getName()}";
    }

    public function getAddLogMessageB(): string
    {
        return "Key given to {$this->getPerson()->getName()}";
    }

    public function getUpdateLogMessageB(): string
    {
        return "Updated key assignment for {$this->getPerson()->getName()}, ";
    }

    public function getRemoveLogMessageB(): string
    {
        return "{$this->getPerson()->getName()} returned key";
    }
}
