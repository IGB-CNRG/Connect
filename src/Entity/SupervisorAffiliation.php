<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\Loggable;
use App\Log\LoggableAffiliationInterface;
use App\Repository\SupervisorAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SupervisorAffiliationRepository::class)]
class SupervisorAffiliation implements HistoricalEntityInterface, LoggableAffiliationInterface
{
    use HistoricalEntityTrait;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'superviseeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    #[Groups(['log:person'])]
    #[Context(context: ['groups'=>['log:related_person']], groups: ['log:person'])]
    private ?Person $supervisor = null;

    #[ORM\ManyToOne(inversedBy: 'supervisorAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ThemeAffiliation $superviseeThemeAffiliation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupervisor(): ?Person
    {
        return $this->supervisor;
    }

    public function setSupervisor(?Person $supervisor): self
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    public function getSuperviseeThemeAffiliation(): ?ThemeAffiliation
    {
        return $this->superviseeThemeAffiliation;
    }

    public function setSuperviseeThemeAffiliation(?ThemeAffiliation $superviseeThemeAffiliation): self
    {
        $this->superviseeThemeAffiliation = $superviseeThemeAffiliation;

        return $this;
    }

    public function getSupervisee(): ?Person
    {
        return $this->superviseeThemeAffiliation->getPerson();
    }

    /* LoggableAffiliationInterface */
    public function getSideA()
    {
        return $this->getSupervisor();
    }

    public function getSideB()
    {
        return $this->getSupervisee();
    }

    public function getAddLogMessageA(): string
    {
        return "Added supervisee {$this->getSupervisee()}";
    }

    public function getUpdateLogMessageA(): string
    {
        return "Updated supervisee assignment with {$this->getSupervisee()}, ";
    }

    public function getRemoveLogMessageA(): string
    {
        return "Removed supervisee {$this->getSupervisee()}";
    }

    public function getAddLogMessageB(): string
    {
        return "Added supervisor {$this->getSupervisor()}";
    }

    public function getUpdateLogMessageB(): string
    {
        return "Updated supervisor assignment with {$this->getSupervisor()}, ";
    }

    public function getRemoveLogMessageB(): string
    {
        return "Removed supervisor assignment with {$this->getSupervisor()}";
    }
}
