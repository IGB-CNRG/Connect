<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Enum\ThemeRole;
use App\Log\Loggable;
use App\Log\LoggableAffiliationInterface;
use App\Repository\ThemeAffiliationRepository;
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
    private Person $person;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'themeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    #[Groups(['log:person'])]
    private Theme $theme;

    #[ORM\ManyToOne(targetEntity: MemberCategory::class, inversedBy: 'themeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    #[Groups(['log:person'])]
    private MemberCategory $memberCategory;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    #[Groups(['log:person'])]
    private ?string $title = null;

    #[ORM\Column(type: 'json', enumType: ThemeRole::class)]
    #[Loggable(type:'array')]
    #[Groups(['log:person'])]
    private array $themeRoles = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['log:person'])]
    private ?string $exitReason = null;

    public function __toString()
    {
        $themeName = $this->getTheme()->getShortName();
        foreach ($this->getThemeRoles() as $index => $role) {
            $themeName .= " " . $role->getDisplayName();
            if ($index < count($this->getThemeRoles()) - 1) {
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

    public function getIsThemeLeader(): bool
    {
        return in_array(ThemeRole::ThemeLeader, $this->getThemeRoles());
    }

    public function getIsThemeAdmin(): bool
    {
        return in_array(ThemeRole::ThemeAdmin, $this->getThemeRoles());
    }

    public function getIsLabManager(): bool
    {
        return in_array(ThemeRole::LabManager, $this->getThemeRoles());
    }


    public function getPerson(): Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getMemberCategory(): MemberCategory
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

    /**
     * @return ThemeRole[]
     */
    public function getThemeRoles(): array
    {
        return $this->themeRoles;
    }

    /**
     * @param ThemeRole[] $themeRoles
     * @return $this
     */
    public function setThemeRoles(array $themeRoles): self
    {
        $this->themeRoles = $themeRoles;

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
}
