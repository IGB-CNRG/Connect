<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\LogSubjectInterface;
use App\Repository\KeyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: KeyRepository::class)]
#[ORM\Table(name: '`key`')]
#[UniqueEntity('name')]
class Key implements LogSubjectInterface, HistoricalEntityInterface
{
    use TimestampableEntity, HistoricalEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['log:person'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'cylinderKey', targetEntity: KeyAffiliation::class, orphanRemoval: true)]
    private Collection $keyAffiliations;

    #[ORM\OneToMany(mappedBy: 'cylinderKey', targetEntity: Log::class)]
    private Collection $logs;

    #[ORM\ManyToMany(targetEntity: Room::class, inversedBy: 'cylinderKeys')]
    private Collection $rooms;

    public function __construct()
    {
        $this->keyAffiliations = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns the display name of this key. If this key opens a single room, returns that room. Otherwise, returns
     *  the key's description/name.
     * @return string
     */
    public function getDisplayName(): string {
        if($this->getRooms()->count() === 1){
            return $this->getRooms()[0]->__toString();
        } elseif($this->getDescription()) {
            return $this->getDescription();
        } else {
            return $this->getName();
        }
    }

    /**
     * @return Collection<int, KeyAffiliation>
     */
    public function getKeyAffiliations(): Collection
    {
        return $this->keyAffiliations;
    }

    public function addKeyAffiliation(KeyAffiliation $keyAffiliation): self
    {
        if (!$this->keyAffiliations->contains($keyAffiliation)) {
            $this->keyAffiliations[] = $keyAffiliation;
            $keyAffiliation->setCylinderKey($this);
        }

        return $this;
    }

    public function removeKeyAffiliation(KeyAffiliation $keyAffiliation): self
    {
        if ($this->keyAffiliations->removeElement($keyAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($keyAffiliation->getCylinderKey() === $this) {
                $keyAffiliation->setCylinderKey(null);
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
            $log->setCylinderKey($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getCylinderKey() === $this) {
                $log->setCylinderKey(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms[] = $room;
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        $this->rooms->removeElement($room);

        return $this;
    }
}
