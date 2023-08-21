<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\LoggableAffiliationInterface;
use App\Repository\SponsorAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: SponsorAffiliationRepository::class)]
class SponsorAffiliation implements HistoricalEntityInterface, LoggableAffiliationInterface
{
    use HistoricalEntityTrait;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sponseeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $sponsor = null;

    #[ORM\ManyToOne(inversedBy: 'sponsorAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ThemeAffiliation $sponseeThemeAffiliation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSponsor(): ?Person
    {
        return $this->sponsor;
    }

    public function setSponsor(?Person $sponsor): self
    {
        $this->sponsor = $sponsor;

        return $this;
    }

    public function getSponseeThemeAffiliation(): ?ThemeAffiliation
    {
        return $this->sponseeThemeAffiliation;
    }

    public function setSponseeThemeAffiliation(?ThemeAffiliation $sponseeThemeAffiliation): self
    {
        $this->sponseeThemeAffiliation = $sponseeThemeAffiliation;

        return $this;
    }

    public function getSponsee(): ?Person
    {
        return $this->sponseeThemeAffiliation->getPerson();
    }

    /* LoggableAffiliationInterface */
    public function getSideA()
    {
        return $this->getSponsor();
    }

    public function getSideB()
    {
        return $this->getSponsee();
    }

    public function getAddLogMessageA(): string
    {
        return "Added sponsee {$this->getSponsee()}";
    }

    public function getUpdateLogMessageA(): string
    {
        return "Updated sponsee assignment with {$this->getSponsee()}, ";
    }

    public function getRemoveLogMessageA(): string
    {
        return "Removed sponsee {$this->getSponsee()}";
    }

    public function getAddLogMessageB(): string
    {
        return "Added sponsor {$this->getSponsor()}";
    }

    public function getUpdateLogMessageB(): string
    {
        return "Updated sponsor assignment with {$this->getSponsor()}, ";
    }

    public function getRemoveLogMessageB(): string
    {
        return "Removed sponsor assignment with {$this->getSponsor()}";
    }
}
