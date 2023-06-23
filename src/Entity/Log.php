<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\LogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'ownedLogs')]
    private ?Person $user = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $text = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'logs')]
    private ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'logs')]
    private ?Theme $theme = null;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'logs')]
    private ?Room $room = null;

    #[ORM\ManyToOne(targetEntity: Key::class, inversedBy: 'logs')]
    private ?Key $cylinderKey = null;

    #[ORM\ManyToOne(targetEntity: Unit::class, inversedBy: 'logs')]
    private ?Unit $unit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $context = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Person
    {
        return $this->user;
    }

    public function setUser(?Person $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
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

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getCylinderKey(): ?Key
    {
        return $this->cylinderKey;
    }

    public function setCylinderKey(?Key $cylinderKey): self
    {
        $this->cylinderKey = $cylinderKey;

        return $this;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): self
    {
        $this->context = $context;

        return $this;
    }
}
