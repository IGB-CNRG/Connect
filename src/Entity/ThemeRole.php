<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\ThemeRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeRoleRepository::class)]
class ThemeRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: ThemeAffiliation::class, mappedBy: 'roles')]
    private Collection $themeAffiliations;

    #[ORM\Column]
    private ?bool $isApprover = null;

    public function __construct()
    {
        $this->themeAffiliations = new ArrayCollection();
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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, ThemeAffiliation>
     */
    public function getThemeAffiliations(): Collection
    {
        return $this->themeAffiliations;
    }

    public function addThemeAffiliation(ThemeAffiliation $themeAffiliation): static
    {
        if (!$this->themeAffiliations->contains($themeAffiliation)) {
            $this->themeAffiliations->add($themeAffiliation);
            $themeAffiliation->addRole($this);
        }

        return $this;
    }

    public function removeThemeAffiliation(ThemeAffiliation $themeAffiliation): static
    {
        if ($this->themeAffiliations->removeElement($themeAffiliation)) {
            $themeAffiliation->removeRole($this);
        }

        return $this;
    }

    public function isIsApprover(): ?bool
    {
        return $this->isApprover;
    }

    public function setIsApprover(bool $isApprover): static
    {
        $this->isApprover = $isApprover;

        return $this;
    }
}
