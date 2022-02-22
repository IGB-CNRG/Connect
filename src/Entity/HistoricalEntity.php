<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait HistoricalEntity
{
    #[ORM\Column(type: 'date', nullable: true)]
    private $startedAt;

    #[ORM\Column(type: 'date', nullable: true)]
    private $endedAt;

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    //TODO add some common helper functions here
    public function isCurrent(): bool
    {
        return $this->getEndedAt() === null;
    }
}