<?php

namespace App\Entity;

use App\Repository\ThemeLeaderAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeLeaderAffiliationRepository::class)]
class ThemeLeaderAffiliation
{
    use HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'themeLeaderAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $person;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'themeLeaderAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $theme;

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
}
