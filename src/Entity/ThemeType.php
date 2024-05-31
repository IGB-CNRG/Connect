<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\ThemeTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeTypeRepository::class)]
class ThemeType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isMember = null;

    #[ORM\Column]
    private ?bool $displayInDirectory = null;

    #[ORM\OneToMany(mappedBy: 'themeType', targetEntity: Theme::class)]
    private Collection $themes;

    public function __construct()
    {
        $this->themes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function isIsMember(): ?bool
    {
        return $this->isMember;
    }

    public function setIsMember(bool $isMember): static
    {
        $this->isMember = $isMember;

        return $this;
    }

    public function isDisplayInDirectory(): ?bool
    {
        return $this->displayInDirectory;
    }

    public function setDisplayInDirectory(bool $displayInDirectory): static
    {
        $this->displayInDirectory = $displayInDirectory;

        return $this;
    }

    /**
     * @return Collection<int, Theme>
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): static
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
            $theme->setThemeType($this);
        }

        return $this;
    }

    public function removeTheme(Theme $theme): static
    {
        if ($this->themes->removeElement($theme)) {
            // set the owning side to null (unless already changed)
            if ($theme->getThemeType() === $this) {
                $theme->setThemeType(null);
            }
        }

        return $this;
    }
}
