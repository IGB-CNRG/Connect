<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $firstName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $lastName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $middleInitial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $netid;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $username;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $uin;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $officeNumber;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $officePhone;

    #[ORM\Column(type: 'text', nullable: true)]
    private $homeAddress;

    #[ORM\Column(type: 'text', nullable: true)]
    private $workAddress;

    #[ORM\Column(type: 'boolean')]
    private $isDrsTrainingComplete;

    #[ORM\Column(type: 'boolean')]
    private $isIgbTrainingComplete;

    #[ORM\Column(type: 'date', nullable: true)]
    private $offerLetterDate;

    #[ORM\Column(type: 'boolean')]
    private $hasGivenKeyDeposit;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: RoomAffiliation::class, orphanRemoval: true)]
    private $roomAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: KeyAffiliation::class, orphanRemoval: true)]
    private $keyAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: ThemeAffiliation::class, orphanRemoval: true)]
    private $themeAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: ThemeLeaderAffiliation::class, orphanRemoval: true)]
    private $themeLeaderAffiliations;

    #[ORM\OneToMany(mappedBy: 'supervisor', targetEntity: SupervisorAffiliation::class, orphanRemoval: true)]
    private $supervisorAffiliations;

    #[ORM\OneToMany(mappedBy: 'supervisee', targetEntity: SupervisorAffiliation::class, orphanRemoval: true)]
    private $superviseeAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: DepartmentAffiliation::class, orphanRemoval: true)]
    private $departmentAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Note::class, orphanRemoval: true)]
    private $notes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Log::class, orphanRemoval: true)]
    private $ownedLogs;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Log::class)]
    private $logs;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: WorkflowProgress::class, orphanRemoval: true)]
    private $workflowProgress;

    #[ORM\ManyToOne(targetEntity: Building::class, inversedBy: 'people')]
    private $officeBuilding;

    #[ORM\Column(type: 'string', length: 255)]
    private $preferredAddress; // TODO create enum for which address is preferred

    public function __construct()
    {
        $this->roomAffiliations = new ArrayCollection();
        $this->keyAffiliations = new ArrayCollection();
        $this->themeAffiliations = new ArrayCollection();
        $this->themeLeaderAffiliations = new ArrayCollection();
        $this->supervisorAffiliations = new ArrayCollection();
        $this->superviseeAffiliations = new ArrayCollection();
        $this->departmentAffiliations = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->ownedLogs = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->workflowProgress = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMiddleInitial(): ?string
    {
        return $this->middleInitial;
    }

    public function setMiddleInitial(?string $middleInitial): self
    {
        $this->middleInitial = $middleInitial;

        return $this;
    }

    public function getNetid(): ?string
    {
        return $this->netid;
    }

    public function setNetid(?string $netid): self
    {
        $this->netid = $netid;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUin(): ?int
    {
        return $this->uin;
    }

    public function setUin(?int $uin): self
    {
        $this->uin = $uin;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOfficeNumber(): ?string
    {
        return $this->officeNumber;
    }

    public function setOfficeNumber(?string $officeNumber): self
    {
        $this->officeNumber = $officeNumber;

        return $this;
    }

    public function getOfficePhone(): ?string
    {
        return $this->officePhone;
    }

    public function setOfficePhone(?string $officePhone): self
    {
        $this->officePhone = $officePhone;

        return $this;
    }

    public function getHomeAddress(): ?string
    {
        return $this->homeAddress;
    }

    public function setHomeAddress(?string $homeAddress): self
    {
        $this->homeAddress = $homeAddress;

        return $this;
    }

    public function getWorkAddress(): ?string
    {
        return $this->workAddress;
    }

    public function setWorkAddress(string $workAddress): self
    {
        $this->workAddress = $workAddress;

        return $this;
    }

    public function getIsDrsTrainingComplete(): ?bool
    {
        return $this->isDrsTrainingComplete;
    }

    public function setIsDrsTrainingComplete(bool $isDrsTrainingComplete): self
    {
        $this->isDrsTrainingComplete = $isDrsTrainingComplete;

        return $this;
    }

    public function getIsIgbTrainingComplete(): ?bool
    {
        return $this->isIgbTrainingComplete;
    }

    public function setIsIgbTrainingComplete(bool $isIgbTrainingComplete): self
    {
        $this->isIgbTrainingComplete = $isIgbTrainingComplete;

        return $this;
    }

    public function getOfferLetterDate(): ?\DateTimeInterface
    {
        return $this->offerLetterDate;
    }

    public function setOfferLetterDate(?\DateTimeInterface $offerLetterDate): self
    {
        $this->offerLetterDate = $offerLetterDate;

        return $this;
    }

    public function getHasGivenKeyDeposit(): ?bool
    {
        return $this->hasGivenKeyDeposit;
    }

    public function setHasGivenKeyDeposit(bool $hasGivenKeyDeposit): self
    {
        $this->hasGivenKeyDeposit = $hasGivenKeyDeposit;

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
            $roomAffiliation->setPerson($this);
        }

        return $this;
    }

    public function removeRoomAffiliation(RoomAffiliation $roomAffiliation): self
    {
        if ($this->roomAffiliations->removeElement($roomAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($roomAffiliation->getPerson() === $this) {
                $roomAffiliation->setPerson(null);
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
            $keyAffiliation->setPerson($this);
        }

        return $this;
    }

    public function removeKeyAffiliation(KeyAffiliation $keyAffiliation): self
    {
        if ($this->keyAffiliations->removeElement($keyAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($keyAffiliation->getPerson() === $this) {
                $keyAffiliation->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ThemeAffiliation>
     */
    public function getThemeAffiliations(): Collection
    {
        return $this->themeAffiliations;
    }

    public function addThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if (!$this->themeAffiliations->contains($themeAffiliation)) {
            $this->themeAffiliations[] = $themeAffiliation;
            $themeAffiliation->setPerson($this);
        }

        return $this;
    }

    public function removeThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if ($this->themeAffiliations->removeElement($themeAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($themeAffiliation->getPerson() === $this) {
                $themeAffiliation->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ThemeLeaderAffiliation>
     */
    public function getThemeLeaderAffiliations(): Collection
    {
        return $this->themeLeaderAffiliations;
    }

    public function addThemeLeaderAffiliation(ThemeLeaderAffiliation $themeLeaderAffiliation): self
    {
        if (!$this->themeLeaderAffiliations->contains($themeLeaderAffiliation)) {
            $this->themeLeaderAffiliations[] = $themeLeaderAffiliation;
            $themeLeaderAffiliation->setPerson($this);
        }

        return $this;
    }

    public function removeThemeLeaderAffiliation(ThemeLeaderAffiliation $themeLeaderAffiliation): self
    {
        if ($this->themeLeaderAffiliations->removeElement($themeLeaderAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($themeLeaderAffiliation->getPerson() === $this) {
                $themeLeaderAffiliation->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SupervisorAffiliation>
     */
    public function getSupervisorAffiliations(): Collection
    {
        return $this->supervisorAffiliations;
    }

    public function addSupervisorAffiliation(SupervisorAffiliation $supervisorAffiliation): self
    {
        if (!$this->supervisorAffiliations->contains($supervisorAffiliation)) {
            $this->supervisorAffiliations[] = $supervisorAffiliation;
            $supervisorAffiliation->setSupervisor($this);
        }

        return $this;
    }

    public function removeSupervisorAffiliation(SupervisorAffiliation $supervisorAffiliation): self
    {
        if ($this->supervisorAffiliations->removeElement($supervisorAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($supervisorAffiliation->getSupervisor() === $this) {
                $supervisorAffiliation->setSupervisor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SupervisorAffiliation>
     */
    public function getSuperviseeAffiliations(): Collection
    {
        return $this->superviseeAffiliations;
    }

    public function addSuperviseeAffiliation(SupervisorAffiliation $superviseeAffiliation): self
    {
        if (!$this->superviseeAffiliations->contains($superviseeAffiliation)) {
            $this->superviseeAffiliations[] = $superviseeAffiliation;
            $superviseeAffiliation->setSupervisee($this);
        }

        return $this;
    }

    public function removeSuperviseeAffiliation(SupervisorAffiliation $superviseeAffiliation): self
    {
        if ($this->superviseeAffiliations->removeElement($superviseeAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($superviseeAffiliation->getSupervisee() === $this) {
                $superviseeAffiliation->setSupervisee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DepartmentAffiliation>
     */
    public function getDepartmentAffiliations(): Collection
    {
        return $this->departmentAffiliations;
    }

    public function addDepartmentAffiliation(DepartmentAffiliation $departmentAffiliation): self
    {
        if (!$this->departmentAffiliations->contains($departmentAffiliation)) {
            $this->departmentAffiliations[] = $departmentAffiliation;
            $departmentAffiliation->setPerson($this);
        }

        return $this;
    }

    public function removeDepartmentAffiliation(DepartmentAffiliation $departmentAffiliation): self
    {
        if ($this->departmentAffiliations->removeElement($departmentAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($departmentAffiliation->getPerson() === $this) {
                $departmentAffiliation->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setPerson($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getPerson() === $this) {
                $note->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Log>
     */
    public function getOwnedLogs(): Collection
    {
        return $this->ownedLogs;
    }

    public function addOwnedLog(Log $ownedLog): self
    {
        if (!$this->ownedLogs->contains($ownedLog)) {
            $this->ownedLogs[] = $ownedLog;
            $ownedLog->setUser($this);
        }

        return $this;
    }

    public function removeOwnedLog(Log $ownedLog): self
    {
        if ($this->ownedLogs->removeElement($ownedLog)) {
            // set the owning side to null (unless already changed)
            if ($ownedLog->getUser() === $this) {
                $ownedLog->setUser(null);
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
            $log->setPerson($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getPerson() === $this) {
                $log->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkflowProgress>
     */
    public function getWorkflowProgress(): Collection
    {
        return $this->workflowProgress;
    }

    public function addWorkflowProgress(WorkflowProgress $workflowProgress): self
    {
        if (!$this->workflowProgress->contains($workflowProgress)) {
            $this->workflowProgress[] = $workflowProgress;
            $workflowProgress->setPerson($this);
        }

        return $this;
    }

    public function removeWorkflowProgress(WorkflowProgress $workflowProgress): self
    {
        if ($this->workflowProgress->removeElement($workflowProgress)) {
            // set the owning side to null (unless already changed)
            if ($workflowProgress->getPerson() === $this) {
                $workflowProgress->setPerson(null);
            }
        }

        return $this;
    }

    public function getOfficeBuilding(): ?Building
    {
        return $this->officeBuilding;
    }

    public function setOfficeBuilding(?Building $officeBuilding): self
    {
        $this->officeBuilding = $officeBuilding;

        return $this;
    }

    public function getPreferredAddress(): ?string
    {
        return $this->preferredAddress;
    }

    public function setPreferredAddress(string $preferredAddress): self
    {
        $this->preferredAddress = $preferredAddress;

        return $this;
    }
}
