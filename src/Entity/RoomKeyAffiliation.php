<?php

namespace App\Entity;

use App\Repository\RoomKeyAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomKeyAffiliationRepository::class)]
class RoomKeyAffiliation
{
    use HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'roomKeyAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $room;

    #[ORM\ManyToOne(targetEntity: Key::class, inversedBy: 'roomKeyAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $cylinderKey;

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

    public function getCylinderKey(): ?Key
    {
        return $this->cylinderKey;
    }

    public function setCylinderKey(?Key $cylinderKey): self
    {
        $this->cylinderKey = $cylinderKey;

        return $this;
    }
}
