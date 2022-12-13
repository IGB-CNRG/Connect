<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\LogSubjectInterface;
use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[UniqueEntity(['number'])]
class Room implements LogSubjectInterface
{
    use TimestampableEntity, HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $number;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $name;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomAffiliation::class, orphanRemoval: true)]
    private Collection $roomAffiliations;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: Log::class)]
    private Collection $logs;

    #[ORM\ManyToMany(targetEntity: Key::class, mappedBy: 'rooms', cascade: ['all'])]
    private Collection $cylinderKeys;

    public function __construct()
    {
        $this->roomAffiliations = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->cylinderKeys = new ArrayCollection();
    }

    public function __toString()
    {
        if ($this->getName()) {
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

    /**
     * @return Collection<int, Key>
     */
    public function getCylinderKeys(): Collection
    {
        return $this->cylinderKeys;
    }

    public function addCylinderKey(Key $cylinderKey): self
    {
        if (!$this->cylinderKeys->contains($cylinderKey)) {
            $this->cylinderKeys[] = $cylinderKey;
            $cylinderKey->addRoom($this);
        }

        return $this;
    }

    public function removeCylinderKey(Key $cylinderKey): self
    {
        if ($this->cylinderKeys->removeElement($cylinderKey)) {
            $cylinderKey->removeRoom($this);
        }

        return $this;
    }
}
