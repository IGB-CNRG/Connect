<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Log\Loggable;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait HistoricalEntityTrait
{
    #[ORM\Column(type: 'date', nullable: true)]
    #[Loggable(displayName: 'start date', type: 'date')]
    private $startedAt;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Loggable(displayName: 'end date', type: 'date')]
    private $endedAt;

    public function getStartedAt(): ?DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    //TODO add some common helper functions here
    public function isCurrent(): bool
    {
        $now = new DateTimeImmutable();
        return $this->wasCurrentAtDate($now);
    }

    public function overlaps($that): bool
    {
        return ($this->getStartedAt() === null || $that->getEndedAt() === null || $that->getEndedAt() > $this->getStartedAt())
               && ($this->getEndedAt() === null || $that->getStartedAt() === null || $this->getEndedAt() > $that->getStartedAt());
    }

    public function wasCurrentAtDate(DateTimeInterface $date): bool
    {
        return ($this->getStartedAt() === null || $this->getStartedAt() < $date)
               && ($this->getEndedAt() === null || $this->getEndedAt() > $date);
    }
}