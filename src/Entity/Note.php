<?php

namespace App\Entity;

use App\Enum\NoteCategory;
use App\Repository\NoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
class Note
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private $person;

    #[ORM\Column(type: 'text')]
    private $text;

    #[ORM\Column(type: 'string', length: 255, enumType: NoteCategory::class)]
    private $type = NoteCategory::General;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'createdNotes')]
    #[ORM\JoinColumn(nullable: false)]
    private $createdBy; // TODO create an enum for note types

    public function getId(): ?int
    {
        return $this->id;
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getType(): ?NoteCategory
    {
        return $this->type;
    }

    public function setType(NoteCategory $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedBy(): ?Person
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Person $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
