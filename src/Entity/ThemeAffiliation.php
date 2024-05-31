<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\Loggable;
use App\Log\LoggableAffiliationInterface;
use App\Repository\ThemeAffiliationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ThemeAffiliationRepository::class)]
class ThemeAffiliation implements HistoricalEntityInterface, LoggableAffiliationInterface
{
    use HistoricalEntityTrait;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'themeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'themeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?Theme $theme = null;

    #[ORM\ManyToOne(targetEntity: MemberCategory::class, inversedBy: 'themeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?MemberCategory $memberCategory = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['log:person'])]
    private ?string $exitReason = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $positionWhenJoined = null;

    #[ORM\OneToMany(mappedBy: 'superviseeThemeAffiliation', targetEntity: SupervisorAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $supervisorAffiliations;

    #[ORM\OneToMany(mappedBy: 'sponseeThemeAffiliation', targetEntity: SponsorAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $sponsorAffiliations;

    #[ORM\ManyToMany(targetEntity: ThemeRole::class, inversedBy: 'themeAffiliations')]
//    #[Loggable(type:'array')]
//    #[Groups(['log:person'])]
    private Collection $roles;

    public function __construct()
    {
        $this->supervisorAffiliations = new ArrayCollection();
        $this->sponsorAffiliations = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function __toString()
    {
        $themeName = $this->getTheme()->getShortName();
        foreach ($this->getRoles() as $index => $role) {
            $themeName .= " " . $role->getName();
            if ($index < count($this->getRoles()) - 1) {
                $themeName .= ',';
            }
        }
        if ($this->getTitle()) {
            return sprintf('%s - %s (%s)', $themeName, $this->getTitle(), $this->getMemberCategory()->getName());
        } else {
            return sprintf('%s (%s)', $themeName, $this->getMemberCategory()->getName());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getMemberCategory(): ?MemberCategory
    {
        return $this->memberCategory;
    }

    public function setMemberCategory(MemberCategory $memberCategory): self
    {
        $this->memberCategory = $memberCategory;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getExitReason(): ?string
    {
        return $this->exitReason;
    }

    public function setExitReason(?string $exitReason): self
    {
        $this->exitReason = $exitReason;

        return $this;
    }

    public function getSideA()
    {
        return $this->getPerson();
    }

    public function getSideB()
    {
        return $this->getTheme();
    }

    public function getAddLogMessageA(): string
    {
        return "Added affiliation with theme {$this->getTheme()} ({$this->getMemberCategory()})";
    }

    public function getUpdateLogMessageA(): string
    {
        return "Updated theme affiliation with {$this->getTheme()}, ";
    }

    public function getRemoveLogMessageA(): string
    {
        return "Removed affiliation with theme {$this->getTheme()} ({$this->getMemberCategory()})";
    }

    public function getAddLogMessageB(): string
    {
        return "Added member affiliation with {$this->getPerson()} ({$this->getMemberCategory()})";
    }

    public function getUpdateLogMessageB(): string
    {
        return "Updated member affiliation for {$this->getPerson()}, ";
    }

    public function getRemoveLogMessageB(): string
    {
        return "Removed member affiliation with {$this->getPerson()} ({$this->getMemberCategory()})";
    }

    public function getPositionWhenJoined(): ?string
    {
        return $this->positionWhenJoined;
    }

    public function setPositionWhenJoined(?string $positionWhenJoined): self
    {
        $this->positionWhenJoined = $positionWhenJoined;

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
            $this->supervisorAffiliations->add($supervisorAffiliation);
            $supervisorAffiliation->setSuperviseeThemeAffiliation($this);
        }

        return $this;
    }

    public function removeSupervisorAffiliation(SupervisorAffiliation $supervisorAffiliation): self
    {
        if ($this->supervisorAffiliations->removeElement($supervisorAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($supervisorAffiliation->getSuperviseeThemeAffiliation() === $this) {
                $supervisorAffiliation->setSuperviseeThemeAffiliation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SponsorAffiliation>
     */
    public function getSponsorAffiliations(): Collection
    {
        return $this->sponsorAffiliations;
    }

    public function addSponsorAffiliation(SponsorAffiliation $sponsorAffiliation): self
    {
        if (!$this->sponsorAffiliations->contains($sponsorAffiliation)) {
            $this->sponsorAffiliations->add($sponsorAffiliation);
            $sponsorAffiliation->setSponseeThemeAffiliation($this);
        }

        return $this;
    }

    public function removeSponsorAffiliation(SponsorAffiliation $sponsorAffiliation): self
    {
        if ($this->sponsorAffiliations->removeElement($sponsorAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($sponsorAffiliation->getSponseeThemeAffiliation() === $this) {
                $sponsorAffiliation->setSponseeThemeAffiliation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ThemeRole>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(ThemeRole $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(ThemeRole $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }
}
