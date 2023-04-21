<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\ExitFormRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ExitFormRepository::class)]
class ExitForm
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $exitReason = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $forwardingEmail = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeImmutable $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getExitReason(): ?string
    {
        return $this->exitReason;
    }

    public function setExitReason(string $exitReason): self
    {
        $this->exitReason = $exitReason;

        return $this;
    }

    public function getForwardingEmail(): ?string
    {
        return $this->forwardingEmail;
    }

    public function setForwardingEmail(?string $forwardingEmail): self
    {
        $this->forwardingEmail = $forwardingEmail;

        return $this;
    }
}
