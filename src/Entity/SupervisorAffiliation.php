<?php

namespace App\Entity;

use App\Repository\SupervisorAffiliationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupervisorAffiliationRepository::class)]
class SupervisorAffiliation
{
    use HistoricalEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'supervisorAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $supervisor;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'superviseeAffiliations')]
    #[ORM\JoinColumn(nullable: false)]
    private $supervisee;

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