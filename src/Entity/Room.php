<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[UniqueEntity(['name'])]
class Room
{
    use HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $number;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomAffiliation::class, orphanRemoval: true)]
    private $roomAffiliations;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomKeyAffiliation::class, orphanRemoval: true)]
    private $roomKeyAffiliations;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: Log::class)]
    private $logs;

    public function __construct()
    {
        $this->roomAffiliations = new ArrayCollection();
        $this->roomKeyAffiliations = new ArrayCollection();
        $this->logs = new ArrayCollection();
    }

    public function __toString(){
        if($this->getName()){
            return sprintf('%s (%s)', $this->getNumber(), $this->getName());
        }
        return $this->getNumber();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, RoomAffiliation>
     */
    public function getRoomAffiliations(): Collection
    {
        return $this->roomAffiliations;
    }

    public function addRoomAffiliation(RoomAffiliation $roomAffiliation): self
    {
        if (!$this->roomAffiliations->contains($roomAffiliation)) {
            $this->roomAffiliations[] = $roomAffiliation;
            $roomAffiliation->setRoom($this);
        }

        return $this;
    }

    public function removeRoomAffiliation(RoomAffiliation $roomAffiliation): self
    {
        if ($this->roomAffiliations->removeElement($roomAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($roomAffiliation->getRoom() === $this) {
                $roomAffiliation->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RoomKeyAffiliation>
     */
    public function getRoomKeyAffiliations(): Collection
    {
        return $this->roomKeyAffiliations;
    }

    public function addRoomKeyAffiliation(RoomKeyAffiliation $roomKeyAffiliation): self
    {
        if (!$this->roomKeyAffiliations->contains($roomKeyAffiliation)) {
            $this->roomKeyAffiliations[] = $roomKeyAffiliation;
            $roomKeyAffiliation->setRoom($this);
        }

        return $this;
    }

    public function removeRoomKeyAffiliation(RoomKeyAffiliation $roomKeyAffiliation): self
    {
        if ($this->roomKeyAffiliations->removeElement($roomKeyAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($roomKeyAffiliation->getRoom() === $this) {
                $roomKeyAffiliation->setRoom(null);
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
            $log->setRoom($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getRoom() === $this) {
                $log->setRoom(null);
            }
        }

        return $this;
    }
}
