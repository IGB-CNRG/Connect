<?php

namespace App\Entity;

use App\Repository\ThemeAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeAffiliationRepository::class)]
class ThemeAffiliation
{
    use HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'themeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $person;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'themeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $theme;

    #[ORM\ManyToOne(targetEntity: MemberCategory::class, inversedBy: 'themeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $memberCategory;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $title;

    #[ORM\Column(type: 'boolean')]
    private $isThemeLeader = false;

    #[ORM\Column(type: 'boolean')]
    private $isThemeAdmin = false;

    #[ORM\Column(type: 'boolean')]
    private $isLabManager = false;

    public function __toString()
    {
        $themeName = $this->getTheme()->getShortName();
        if($this->getIsThemeLeader()){
            $themeName .= " Theme Leader";
        }
        if($this->getIsThemeAdmin()){
            $themeName .= " Theme Admin";
        }
        if($this->getIsLabManager()){
            $themeName .= " Lab Manager";
        }
        if($this->getTitle()){
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

    public function setPerson(?Person $person): self
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

    public function setMemberCategory(?MemberCategory $memberCategory): self
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

    public function getIsThemeLeader(): ?bool
    {
        return $this->isThemeLeader;
    }

    public function setIsThemeLeader(bool $isThemeLeader): self
    {
        $this->isThemeLeader = $isThemeLeader;

        return $this;
    }

    public function getIsThemeAdmin(): ?bool
    {
        return $this->isThemeAdmin;
    }

    public function setIsThemeAdmin(bool $isThemeAdmin): self
    {
        $this->isThemeAdmin = $isThemeAdmin;

        return $this;
    }

    public function getIsLabManager(): ?bool
    {
        return $this->isLabManager;
    }

    public function setIsLabManager(bool $isLabManager): self
    {
        $this->isLabManager = $isLabManager;

        return $this;
    }
}
