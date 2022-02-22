<?php

namespace App\Entity;

use App\Repository\RoomAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomAffiliationRepository::class)]
class RoomAffiliation
{
    use HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'roomAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $room;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'roomAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $person;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }
}