<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use DateTimeInterface;

interface HistoricalEntityInterface
{
    public function getStartedAt(): ?DateTimeInterface;

    public function setStartedAt(?DateTimeInterface $startedAt): self;

    public function getEndedAt(): ?DateTimeInterface;

    public function setEndedAt(?DateTimeInterface $endedAt): self;

    public function isCurrent(): bool;

    public function isPast(): bool;

    public function overlaps($that): bool;

    public function wasCurrentAtDate(DateTimeInterface $date): bool;
}