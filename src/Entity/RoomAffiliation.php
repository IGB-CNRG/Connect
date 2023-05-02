<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\Loggable;
use App\Log\LoggableAffiliationInterface;
use App\Repository\RoomAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RoomAffiliationRepository::class)]
class RoomAffiliation implements HistoricalEntityInterface, LoggableAffiliationInterface
{
    use HistoricalEntityTrait;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'roomAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?Room $room = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'roomAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getPerson(): Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getSideA()
    {
        return $this->getPerson();
    }

    public function getSideB()
    {
        return $this->getRoom();
    }

    public function getAddLogMessageA(): string
    {
        return "Added room {$this->getRoom()}";
    }

    public function getUpdateLogMessageA(): string
    {
        return "Updated room affiliation with {$this->getRoom()}, ";
    }

    public function getRemoveLogMessageA(): string
    {
        return "Removed room {$this->getRoom()}";
    }

    public function getAddLogMessageB(): string
    {
        return "Added person {$this->getPerson()}";
    }

    public function getUpdateLogMessageB(): string
    {
        return "Updated member affiliation for {$this->getPerson()}, ";
    }

    public function getRemoveLogMessageB(): string
    {
        return "Removed person {$this->getPerson()}";
    }
}
