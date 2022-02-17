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
}
