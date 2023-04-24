<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\MemberCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MemberCategoryRepository::class)]
class MemberCategory
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['log:person'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['log:person'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'memberCategory', targetEntity: ThemeAffiliation::class, orphanRemoval: true)]
    private Collection $themeAffiliations;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $shortName = null;

    #[ORM\Column]
    private ?bool $canSupervise = null;

    #[ORM\Column]
    private ?bool $needsCertificates = null;

    public function __construct()
    {
        $this->themeAffiliations = new ArrayCollection();
    }

    public function __toString()
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

    public function setName(string $name): self
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

    public function addThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if (!$this->themeAffiliations->contains($themeAffiliation)) {
            $this->themeAffiliations[] = $themeAffiliation;
            $themeAffiliation->setMemberCategory($this);
        }

        return $this;
    }

    public function removeThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if ($this->themeAffiliations->removeElement($themeAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($themeAffiliation->getMemberCategory() === $this) {
                $themeAffiliation->setMemberCategory(null);
            }
        }

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function isCanSupervise(): ?bool
    {
        return $this->canSupervise;
    }

    public function setCanSupervise(bool $canSupervise): self
    {
        $this->canSupervise = $canSupervise;

        return $this;
    }

    public function isNeedsCertificates(): ?bool
    {
        return $this->needsCertificates;
    }

    public function setNeedsCertificates(bool $needsCertificates): self
    {
        $this->needsCertificates = $needsCertificates;

        return $this;
    }
}
