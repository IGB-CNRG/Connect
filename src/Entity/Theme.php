<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\LogSubjectInterface;
use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme implements LogSubjectInterface, HistoricalEntityInterface
{
    use HistoricalEntityTrait;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['log:person'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['log:person'])]
    private ?string $shortName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isNonResearch = false;

    #[ORM\OneToMany(mappedBy: 'theme', targetEntity: ThemeAffiliation::class, orphanRemoval: true)]
    private Collection $themeAffiliations;

    #[ORM\OneToMany(mappedBy: 'theme', targetEntity: Log::class)]
    private Collection $logs;

    #[ORM\Column(type: 'boolean')]
    private bool $isOutsideGroup = false;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subgroups')]
    private ?self $parentTheme = null;

    #[ORM\OneToMany(mappedBy: 'parentTheme', targetEntity: self::class)]
    private Collection $subgroups;

    public function __construct()
    {
        $this->themeAffiliations = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->subgroups = new ArrayCollection();
    }

    public function __toString()
    {
        if($this->getParentTheme()){
            return "{$this->getShortName()} ({$this->getParentTheme()})";
        }
        return $this->getShortName();
    }

    //MARK: - Getters/Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getIsNonResearch(): bool
    {
        return $this->isNonResearch;
    }

    public function setIsNonResearch(bool $isNonResearch): self
    {
        $this->isNonResearch = $isNonResearch;

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
            $themeAffiliation->setTheme($this);
        }

        return $this;
    }

    public function removeThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if ($this->themeAffiliations->removeElement($themeAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($themeAffiliation->getTheme() === $this) {
                $themeAffiliation->setTheme(null);
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
            $log->setTheme($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getTheme() === $this) {
                $log->setTheme(null);
            }
        }

        return $this;
    }

    public function getIsOutsideGroup(): ?bool
    {
        return $this->isOutsideGroup;
    }

    public function setIsOutsideGroup(bool $isOutsideGroup): self
    {
        $this->isOutsideGroup = $isOutsideGroup;

        return $this;
    }

    public function getParentTheme(): ?self
    {
        return $this->parentTheme;
    }

    public function setParentTheme(?self $parentTheme): self
    {
        $this->parentTheme = $parentTheme;

        return $this;
    }

    /**
     * @return Collection<int, self>|Theme[]
     */
    public function getSubgroups(): Collection
    {
        return $this->subgroups;
    }

    public function addSubgroup(self $subgroup): self
    {
        if (!$this->subgroups->contains($subgroup)) {
            $this->subgroups->add($subgroup);
            $subgroup->setParentTheme($this);
        }

        return $this;
    }

    public function removeSubgroup(self $subgroup): self
    {
        if ($this->subgroups->removeElement($subgroup)) {
            // set the owning side to null (unless already changed)
            if ($subgroup->getParentTheme() === $this) {
                $subgroup->setParentTheme(null);
            }
        }

        return $this;
    }
}
