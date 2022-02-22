<?php

namespace App\Entity;

use App\Repository\KeyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KeyRepository::class)]
#[ORM\Table(name: '`key`')]
class Key
{
    use HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $description;

    #[ORM\OneToMany(mappedBy: 'cylinderKey', targetEntity: RoomKeyAffiliation::class, orphanRemoval: true)]
    private $roomKeyAffiliations;

    #[ORM\OneToMany(mappedBy: 'cylinderKey', targetEntity: KeyAffiliation::class, orphanRemoval: true)]
    private $keyAffiliations;

    #[ORM\OneToMany(mappedBy: 'cylinderKey', targetEntity: Log::class)]
    private $logs;

    public function __construct()
    {
        $this->roomKeyAffiliations = new ArrayCollection();
        $this->keyAffiliations = new ArrayCollection();
        $this->logs = new ArrayCollection();
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
            $roomKeyAffiliation->setCylinderKey($this);
        }

        return $this;
    }

    public function removeRoomKeyAffiliation(RoomKeyAffiliation $roomKeyAffiliation): self
    {
        if ($this->roomKeyAffiliations->removeElement($roomKeyAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($roomKeyAffiliation->getCylinderKey() === $this) {
                $roomKeyAffiliation->setCylinderKey(null);
            }
        }

        return $this;
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
}
