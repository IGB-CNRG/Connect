<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Enum\DocumentCategory;
use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[Vich\Uploadable]
class Document
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $fileName;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private $person;

    #[Vich\UploadableField(mapping: 'person_document', fileNameProperty: 'fileName', mimeType: 'mimeType', originalName: 'originalName')]
    private $file;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mimeType;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $originalName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $displayName;

    #[ORM\Column(type: 'string', length: 255, enumType: DocumentCategory::class)]
    private $type = DocumentCategory::Other;

    #[ORM\ManyToOne(targetEntity: Person::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $uploadedBy;

    #[Pure] public function __toString()
    {
        if($this->getDisplayName()){
            return $this->getDisplayName();
        } else {
            return $this->getOriginalName();
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;
        if (null !== $file) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

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

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getType(): ?DocumentCategory
    {
        return $this->type;
    }

    public function setType(DocumentCategory $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUploadedBy(): ?Person
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?Person $uploadedBy): self
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }
}
