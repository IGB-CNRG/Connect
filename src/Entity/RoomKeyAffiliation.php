<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\RoomKeyAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: RoomKeyAffiliationRepository::class)]
class RoomKeyAffiliation
{
    use TimestampableEntity, HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'roomKeyAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private Room $room;

    #[ORM\ManyToOne(targetEntity: Key::class, inversedBy: 'roomKeyAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private Key $cylinderKey;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getCylinderKey(): Key
    {
        return $this->cylinderKey;
    }

    public function setCylinderKey(Key $cylinderKey): self
    {
        $this->cylinderKey = $cylinderKey;

        return $this;
    }
}
