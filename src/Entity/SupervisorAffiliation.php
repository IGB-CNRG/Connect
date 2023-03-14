<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\Loggable;
use App\Repository\SupervisorAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SupervisorAffiliationRepository::class)]
class SupervisorAffiliation implements HistoricalEntityInterface
{
    use TimestampableEntity, HistoricalEntityTrait;

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

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'supervisorAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Loggable]
    #[Groups(['log:person'])]
    #[Context(context: ['groups'=>['log:related_person']], groups: ['log:person'])]
    private ?Person $supervisee = null;

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

    public function getSupervisee(): ?Person
    {
        return $this->supervisee;
    }

    public function setSupervisee(?Person $supervisee): self
    {
        $this->supervisee = $supervisee;

        return $this;
    }
}
