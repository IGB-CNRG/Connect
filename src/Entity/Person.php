<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\SimpleSearchFilter;
use App\Log\Loggable;
use App\Log\LoggableManyRelation;
use App\Log\LogSubjectInterface;
use App\Repository\PersonRepository;
use App\Workflow\Membership;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[Vich\Uploadable]
#[UniqueEntity('username', 'A person with this username already exists')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => 'person:read']
)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'username' => 'exact', 'uin' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'firstName',
    'lastName',
], arguments: ['searchParameterName' => 'search'])]
class Person implements UserInterface, PasswordAuthenticatedUserInterface, Serializable, LogSubjectInterface
    // TODO Is it a bug that we have to implement PasswordAuthenticatedUserInterface even though this entity doesn't handle authentication?
{
    use TimestampableEntity;

    public const USER_ROLES = [
        'Connect Admin' => 'ROLE_ADMIN',
        //        'Key Manager' => 'ROLE_KEY_MANAGER',
        'Certificate Manager' => 'ROLE_CERTIFICATE_MANAGER',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['log:person', 'log:related_person'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person', 'log:related_person', 'person:read'])]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person', 'log:related_person', 'person:read'])]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?string $middleInitial = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable(displayName: 'netID')]
    #[Groups(['log:person', 'log:related_person', 'person:read'])]
    private ?string $netid = null;

    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: true)]
    #[Loggable]
    #[Groups(['log:person', 'person:read'])]
    private ?string $username = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Loggable(displayName: 'UIN')]
    #[Groups(['log:person', 'person:read'])]
    private ?int $uin = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person', 'person:read'])]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?string $officeNumber = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?string $officePhone = null;

    #[ORM\Column(type: 'boolean')]
    #[Loggable(displayName: 'key deposit')]
    #[Groups(['log:person'])]
    private bool $hasGivenKeyDeposit = false;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: RoomAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    #[LoggableManyRelation]
    #[Groups(['log:person'])]
    private Collection $roomAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: KeyAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['startedAt' => 'ASC'])]
    #[LoggableManyRelation]
    #[Groups(['log:person'])]
    private Collection $keyAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: ThemeAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['startedAt' => 'DESC'])]
    #[LoggableManyRelation]
    #[Groups(['log:person'])]
    private Collection $themeAffiliations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Log::class, orphanRemoval: true)]
    private Collection $ownedLogs;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Log::class)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $logs;

    #[ORM\ManyToOne(targetEntity: Building::class, inversedBy: 'people')]
    #[Groups(['log:person'])]
    private ?Building $officeBuilding;

    #[ORM\Column(type: 'json')]
    #[Loggable(type: 'array')]
    #[Groups(['log:person'])]
    private ?array $roles = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person', 'person:read'])]
    private ?string $preferredFirstName = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Document::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $documents;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable(displayName: 'portrait', details: false)]
    private ?string $imageName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $mimeType;

    #[Vich\UploadableField(mapping: 'person_image', fileNameProperty: 'imageName', size: 'imageSize', mimeType: 'mimeType')]
    #[Ignore]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $imageSize;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Slug(fields: ['firstName', 'lastName'])]
    #[Groups(['person:read'])]
    private ?string $slug = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['log:person'])]
    private ?string $otherAddress = null;

    #[ORM\Column(length: 255)]
    #[Groups(['log:person'])]
    private ?string $membershipStatus = Membership::PLACE_NEED_ENTRY_FORM;

    #[ORM\Column(nullable: true)]
    #[Groups(['log:person'])]
    private ?bool $officeWorkOnly = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['log:person'])]
    private ?DateTimeInterface $membershipUpdatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $membershipNote = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?ExitForm $exitForm = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?Unit $unit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $otherUnit = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $lastReviewedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastReviewedAt = null;

    #[ORM\OneToMany(mappedBy: 'supervisor', targetEntity: SupervisorAffiliation::class, orphanRemoval: true)]
    private Collection $superviseeAffiliations;

    #[ORM\OneToMany(mappedBy: 'sponsor', targetEntity: SponsorAffiliation::class, orphanRemoval: true)]
    private Collection $sponseeAffiliations;

    public function __construct()
    {
        $this->roomAffiliations = new ArrayCollection();
        $this->keyAffiliations = new ArrayCollection();
        $this->themeAffiliations = new ArrayCollection();
        $this->ownedLogs = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->superviseeAffiliations = new ArrayCollection();
        $this->sponseeAffiliations = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    /* Helper Functions */

    #[Groups(['person:read'])]
    public function getName(): string
    {
        if ($this->getPreferredFirstName()) {
            return $this->getPreferredFirstName().' '.$this->getLastName();
        }

        return $this->getFirstName().' '.$this->getLastName(); // TODO this should be a little smarter
    }

    #[Groups(['person:read'])]
    public function getIsCurrent(): bool
    {
        return $this->getThemeAffiliations()->filter(function (ThemeAffiliation $themeAffiliation) {
                return $themeAffiliation->isCurrent();
            })->count() > 0;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getThemeAdminThemeAffiliations(): Collection
    {
        // todo unit test this
        return $this->getThemeAffiliations()->filter(function (ThemeAffiliation $themeAffiliation) {
            return $themeAffiliation->isCurrent() && $themeAffiliation->getIsThemeAdmin();
        });
    }

    public function getLabManagerThemeAffiliations(): Collection
    {
        // todo unit test this
        return $this->getThemeAffiliations()->filter(function (ThemeAffiliation $themeAffiliation) {
            return $themeAffiliation->isCurrent() && $themeAffiliation->getIsLabManager();
        });
    }

    /* Getters/Setters */

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
     * @return Collection<int, RoomAffiliation>|RoomAffiliation[]
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
        $this->roomAffiliations->removeElement($roomAffiliation);

        return $this;
    }

    /**
     * @return Collection<int, KeyAffiliation>|KeyAffiliation[]
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
        $this->keyAffiliations->removeElement($keyAffiliation);

        return $this;
    }

    /**
     * @return Collection<int, ThemeAffiliation>|ThemeAffiliation[]
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
        $this->themeAffiliations->removeElement($themeAffiliation);

        return $this;
    }

    /**
     * @return Collection<int, Log>|Log[]
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
     * @return Collection<int, Log>|Log[]
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

    public function getOfficeBuilding(): ?Building
    {
        return $this->officeBuilding;
    }

    public function setOfficeBuilding(?Building $officeBuilding): self
    {
        $this->officeBuilding = $officeBuilding;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getDisplayRoles(): array
    {
        return $this->roles;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPassword(): ?string
    {
        return ''; // TODO I have no idea why it's necessary to implement this
    }

    public function getPreferredFirstName(): ?string
    {
        return $this->preferredFirstName;
    }

    public function setPreferredFirstName(?string $preferredFirstName): self
    {
        $this->preferredFirstName = $preferredFirstName;

        return $this;
    }

    /**
     * @return Collection<int, Document>|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setPerson($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getPerson() === $this) {
                $document->setPerson(null);
            }
        }

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function serialize()
    {
        return serialize([
            'id' => $this->id,
            'username' => $this->username,
        ]);
    }

    public function unserialize(string $serialized)
    {
        $data = unserialize($serialized);
        $this->id = $data['id'];
        $this->username = $data['username'];
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
    }

    public function getOtherAddress(): ?string
    {
        return $this->otherAddress;
    }

    public function setOtherAddress(?string $otherAddress): self
    {
        $this->otherAddress = $otherAddress;

        return $this;
    }

    public function getMemberCategories(): array
    {
        return array_map(
            function (ThemeAffiliation $themeAffiliation) {
                return $themeAffiliation->getMemberCategory();
            },
            $this->getThemeAffiliations()->toArray()
        );
    }

    public function getMembershipStatus(): ?string
    {
        return $this->membershipStatus;
    }

    public function setMembershipStatus(string $membershipStatus): self
    {
        $this->membershipStatus = $membershipStatus;

        return $this;
    }

    public function isOfficeWorkOnly(): ?bool
    {
        return $this->officeWorkOnly;
    }

    public function setOfficeWorkOnly(?bool $officeWorkOnly): self
    {
        $this->officeWorkOnly = $officeWorkOnly;

        return $this;
    }

    public function getMembershipUpdatedAt(): ?DateTimeInterface
    {
        return $this->membershipUpdatedAt;
    }

    public function setMembershipUpdatedAt(DateTimeInterface $membershipUpdatedAt): self
    {
        $this->membershipUpdatedAt = $membershipUpdatedAt;

        return $this;
    }

    public function getMembershipNote(): ?string
    {
        return $this->membershipNote;
    }

    public function setMembershipNote(?string $membershipNote): self
    {
        $this->membershipNote = $membershipNote;

        return $this;
    }

    public function getExitForm(): ?ExitForm
    {
        return $this->exitForm;
    }

    public function setExitForm(?ExitForm $exitForm): self
    {
        $this->exitForm = $exitForm;

        return $this;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getOtherUnit(): ?string
    {
        return $this->otherUnit;
    }

    public function setOtherUnit(?string $otherUnit): self
    {
        $this->otherUnit = $otherUnit;

        return $this;
    }

    public function getLastReviewedBy(): ?self
    {
        return $this->lastReviewedBy;
    }

    public function setLastReviewedBy(?self $lastReviewedBy): self
    {
        $this->lastReviewedBy = $lastReviewedBy;

        return $this;
    }

    public function getLastReviewedAt(): ?\DateTimeImmutable
    {
        return $this->lastReviewedAt;
    }

    public function setLastReviewedAt(?\DateTimeImmutable $lastReviewedAt): self
    {
        $this->lastReviewedAt = $lastReviewedAt;

        return $this;
    }

    /**
     * @return Collection<int, ThemeAffiliation>
     */
    public function getSuperviseeAffiliations(): Collection
    {
        return $this->superviseeAffiliations;
    }

    public function addSuperviseeAffiliation(SupervisorAffiliation $supervisorAffiliation): self
    {
        if (!$this->superviseeAffiliations->contains($supervisorAffiliation)) {
            $this->superviseeAffiliations->add($supervisorAffiliation);
            $supervisorAffiliation->setSupervisor($this);
        }

        return $this;
    }

    public function removeSuperviseeAffiliation(SupervisorAffiliation $supervisorAffiliation): self
    {
        if ($this->superviseeAffiliations->removeElement($supervisorAffiliation)) {
            if ($supervisorAffiliation->getSupervisor() === $this) {
                $supervisorAffiliation->setSupervisor(null);
            }
        }

        return $this;
    }

    public function getSupervisorAffiliations(): array
    {
        return array_merge(
            ...array_map
            (
                fn(ThemeAffiliation $affiliation) => $affiliation->getSupervisorAffiliations()->toArray(),
                $this->getThemeAffiliations()->toArray()
            )
        );
    }

    /**
     * @return Collection<int, SponsorAffiliation>
     */
    public function getSponseeAffiliations(): Collection
    {
        return $this->sponseeAffiliations;
    }

    public function addSponseeAffiliation(SponsorAffiliation $sponseeAffiliation): self
    {
        if (!$this->sponseeAffiliations->contains($sponseeAffiliation)) {
            $this->sponseeAffiliations->add($sponseeAffiliation);
            $sponseeAffiliation->setSponsor($this);
        }

        return $this;
    }

    public function removeSponseeAffiliation(SponsorAffiliation $sponseeAffiliation): self
    {
        if ($this->sponseeAffiliations->removeElement($sponseeAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($sponseeAffiliation->getSponsor() === $this) {
                $sponseeAffiliation->setSponsor(null);
            }
        }

        return $this;
    }

    public function getSponsorAffiliations(): array
    {
        return array_merge(
            ...array_map
            (
                fn(ThemeAffiliation $affiliation) => $affiliation->getSponsorAffiliations()->toArray(),
                $this->getThemeAffiliations()->toArray()
            )
        );
    }
}
